<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequestHistoryModel extends Model
{
    use HasFactory;

    protected $table = 'service_request_history';
    protected $primaryKey = 'history_id';

    protected $fillable = [
        'history_id',
        'service_request_id',
        'user_id',
        'previous_status',
        'new_status',
        'notes',
        'changed_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequestModel::class, 'service_request_id', 'service_request_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
