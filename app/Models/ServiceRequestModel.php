<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceRequestModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSED = 'processed';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'service_request';
    protected $primaryKey = 'service_request_id';

    protected $fillable = [
        'service_request_id',
        'request_number',
        'user_id',
        'service_type_id',
        'district_id',
        'applicant_name',
        'applicant_nik',
        'applicant_phone',
        'applicant_address',
        'purpose',
        'status',
        'notes',
        'submitted_at',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public static function validStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_PROCESSED,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function finalStatuses(): array
    {
        return [
            self::STATUS_APPROVED,
            self::STATUS_CANCELLED,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function serviceType()
    {
        return $this->belongsTo(ServiceTypeModel::class, 'service_type_id', 'service_type_id');
    }

    public function district()
    {
        return $this->belongsTo(DistrictModel::class, 'district_id', 'district_id');
    }

    public function histories()
    {
        return $this->hasMany(ServiceRequestHistoryModel::class, 'service_request_id', 'service_request_id');
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
