<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    /**
     * @var mixed
     */
    protected $table = 'registrations'; // 数据库中的表名
    protected $primaryKey = 'id'; // 主键
    public $timestamps = true; // 自动管理 created_at 和 updated_at

    // 允许批量赋值的字段
    protected $fillable = [
        'student_id',
        'event_name',
        'student_name',
        'class',
        'major',
    ];

    // 设置日期字段的格式
    protected $dates = [
        'registered_at',
    ];

    // 定义与 Event 的关系
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // 定义与 User 的关系 (假设有 User 模型)
    public function Student()
    {
        return $this->belongsTo(Student::class);
    }
}
