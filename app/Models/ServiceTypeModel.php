<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceTypeModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'service_type';
    protected $primaryKey = 'service_type_id';

    protected $fillable = [
        'service_type_id',
        'service_code',
        'service_name',
        'description',
        'is_active',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequestModel::class, 'service_type_id', 'service_type_id');
    }

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
