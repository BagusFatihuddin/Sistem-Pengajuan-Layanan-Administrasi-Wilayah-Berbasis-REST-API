<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProvinceModel extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'province';
    protected $primaryKey = 'province_id';

    protected $fillable = [
        'province_id',
        'province_code',
        'province_name',
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
