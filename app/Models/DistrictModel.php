<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistrictModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'district';
    protected $primaryKey = 'district_id';

    protected $fillable = [
        'district_id',
        'city_id',
        'district_code',
        'district_name',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequestModel::class, 'district_id', 'district_id');
    }
}
