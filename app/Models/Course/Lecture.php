<?php

namespace App\Models\Course;

use App\Models\User\Teacher;
use App\Scopes\Local\NextDaysTrait;
use Illuminate\Database\Eloquent\Model;

class Lecture extends Model
{
    use NextDaysTrait;

    /**
     * Field name to use in the NextDays scope
     *
     * @var string
     */
    protected $timeField = 'start_at';

    protected $dates = ['start_at'];

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeOrderByLatest($query)
    {
        return $query->orderBy('start_at', 'desc');
    }
}
