<?php

namespace Bigin\History\Entities;

use Illuminate\Database\Eloquent\Model;

class AuditHistory extends Model
{
	/**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'audit_histories';

    protected $fillable = [
        'target_type',
        'target_id',
        'user_id',
        'type',
        'result',
        'details',
        'activity_id'
    ];

    /**
     * The date fields for the model.clear
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * [setClientIdAttribute description]
     * @param $value
     */
    public function setTargetIdAttribute($value)
    {
        $this->attributes['target_id'] = (int)$value;
    }

    /**
     * [setActivityIdAttribute description]
     * @param $value
     */
    public function setActivityIdAttribute($value)
    {
        $this->attributes['activity_id'] = (int)$value;
    }

    /**
     * [getCreatedAtAttribute description]
     * @param  $value
     * @return string
     */
    public function getCreatedAtAttribute($value)
    {
        return $value ? date('m/d/Y H:i', strtotime($value)) : '';
    }
}
