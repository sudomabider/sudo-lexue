<?php

namespace App\Http\Controllers\Student;

use App\Events\LecturePurchased;
use App\Models\Course\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    private $student;

    /**
     * OrderController constructor.
     */
    public function __construct()
    {
        $this->student = authUser();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $raw = $this->student->lectures();
        $lecturesDesc = $raw->orderByLatest()->get();
        $lecturesAsc = $raw->orderByEarliest()->get();

        $upcoming = $lecturesAsc->filter(function($lecture) {
            return $lecture->start_time >= Carbon::now();
        });

        $ongoing = $lecturesDesc->filter(function($lecture) {
            return ($lecture->start_time < Carbon::now() && $lecture->end_time >= Carbon::now());
        });

        $finished = $lecturesDesc->filter(function($lecture) {
            return $lecture->end_time < Carbon::now();
        });

        return $this->frontView('wechat.orders.index', compact('upcoming', 'ongoing', 'finished'));
    }

    public function show()
    {
        //
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function pay($id)
    {
        $order = Order::find($id);

        if($order->is_lecture == 1) {
            $tradeInfo = array(
                'trade_type'       => 'JSAPI',
                'body'             => '乐学云直播课 '.$order->lecture->name,
                'detail'           => $order->lecture->start_time.' '.$order->lecture->length.' 分钟',
                'out_trade_no'     => $order->trade_no,
                'total_fee'        => $order->total * 100,
                'notify_url'       => route('m.students::orders.callback.lecture'), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'openid'           => $this->student->wechat_id
            );
        }

        console_log($tradeInfo);

        $attributes = \WechatCashier::prepay($tradeInfo);

        $apiList = array('chooseWXPay');
        $wxConfigs = \WechatCashier::config($apiList);

        return $this->frontView('wechat.orders.pay', compact('order', 'attributes', 'wxConfigs', 'tradeInfo'));
    }

    public function displayResult($id)
    {
        $order = Order::find($id);

        return $this->frontView('wechat.orders.index', compact('order'));
    }

    /**
     * @return mixed
     */
    public function handleLecturePaymentCallback()
    {
        $response = app('wechat')->payment->handleNotify(function($notify, $successful) {
            $order = Order::where('trade_no', $notify->out_trade_no)->first();

            if($order === null)
                return 'Order does not exist.';

            if($order->paid == 1)
                return true;

            if($successful) {
                $order->transaction_id = $notify->transaction_id;
                $order->paid_at = time();
                $order->paid = 1;

                event(new LecturePurchased($order));
            } else {
                $order->paid= 0;
            }
            $order->save();

            return true;
        });

        return $response;
    }
}
