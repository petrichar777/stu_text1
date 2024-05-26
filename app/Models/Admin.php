<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = "admins"; // 确保数据库中的表名是复数形式
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $guarded = [];

    protected $fillable = [
        'admin_name',
        'major',
        'password',
    ];

}
