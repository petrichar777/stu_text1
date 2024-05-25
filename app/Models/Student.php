<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Student extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = "students"; // 确保数据库中的表名是复数形式
    public $timestamps = false;
    protected $primaryKey = "id";
    protected $guarded = [];

    protected $fillable = [
        'student_id',
        'password',
        'student_name',
        'major',
        'class',
    ];




    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * 验证用户的用户名和密码。
     *
     * @param  string  $student_id
     * @param  string  $password
     * @return bool
     */
    public static function validateCredentials($student_id, $password)
    {
        $user = self::where('student_id', $student_id)->first();

        if ($user && Hash::check($password, $user->password)) {
            // 如果密码正确，返回 true
            return true;
        }

        // 如果用户不存在或密码不匹配，返回 false
        return false;
    }


}
