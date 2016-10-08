<?php
/**
 *
 *
 *   ______                        _____           __
 *  /_  __/__  ____ _____ ___     / ___/__  ______/ /___
 *   / / / _ \/ __ `/ __ `__ \    \__ \/ / / / __  / __ \
 *  / / /  __/ /_/ / / / / / /   ___/ / /_/ / /_/ / /_/ /
 * /_/  \___/\__,_/_/ /_/ /_/   /____/\__,_/\__,_/\____/
 *
 *
 *
 * Filename->Payment.php
 * Project->lexue
 * Description->The service to handle wechat payments.
 *
 * Created by DM on 16/10/8 上午12:04.
 * Copyright 2016 Team Sudo. All rights reserved.
 *
 */
namespace App\Services\Wechat;

use EasyWeChat\Payment\Order;

class PaymentHandler
{
    /**
     * @var \EasyWeChat\Foundation\Application
     */
    private $wechat;

    /**
     * @var \EasyWeChat\Payment\Payment
     */
    private $payment;

    public function __construct()
    {
        $this->wechat = app('wechat');

        $this->payment = $this->wechat->payment;
    }

    /**
     * Prepare to get local payment configs.
     *
     * @param $tradeInfo
     * @return bool|mixed
     */
    public function prepay($tradeInfo)
    {
        $order = new Order($tradeInfo);

        $result = $this->payment->prepare($order);

        if ($result->return_code == 'SUCCESS' && $result->result_code == 'SUCCESS'){
            $prepayId = $result->prepay_id;
        }
        else return false;

        return $this->payment->configForPayment($prepayId);
    }
}