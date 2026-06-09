<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CityModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'city';
    protected $primaryKey = 'city_id';

    protected $fillable = [
        'city_id',
        'province_id',
        'city_code',
        'city_name',
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
}