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
     * Grab the revision history for the model that is calling
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function audithistoryable()
    {
        return $this->morphTo();
    }
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
     * User Responsible.
     *
     * @return bool|User
     */
    public function userResponsible()
    {
        if (empty($this->user_id)) {
            return false;
        }
        $userModel = app('config')->get('auth.model');

        if (empty($userModel)) {
            $userModel = app('config')->get('auth.providers.users.model');
            if (empty($userModel)) {
                return false;
            }
        }

        if (!class_exists($userModel)) {
            return false;
        }

        return $userModel::find($this->user_id);
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
