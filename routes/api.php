<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Wwjcontroller;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//学生登录
Route::post('student/login', [\App\Http\Controllers\Wwjcontroller::class, "login"]);
//学生注册
Route::post('student/register', [\App\Http\Controllers\Wwjcontroller::class, 'register']);
//学生/管理员登出
Route::post('logout', [Wwjcontroller::class, 'logout']);
//管理员登录
Route::post('admin/login', [\App\Http\Controllers\Wwjcontroller::class, "adminlogin"]);
//学生报名参加比赛
Route::post('student/signup', [\App\Http\Controllers\Wwjcontroller::class, "student_signup"]);
//学生删除信息
Route::delete('student/delete', [\App\Http\Controllers\Wwjcontroller::class, "SxqstudentDelete"]);
//学生查看信息
Route::post('student/select', [\App\Http\Controllers\Wwjcontroller::class, "selectStudent"]);
//管理员查看信息
Route::post('admin/select', [\App\Http\Controllers\Wwjcontroller::class, "selectAdmin"]);
//管理员查看单个学生报名信息
Route::post('admin/selectonestu', [\App\Http\Controllers\Wwjcontroller::class, "selectonestu"]);
//管理员增加参数学生
Route::post('admin/signup', [\App\Http\Controllers\Wwjcontroller::class, "Admin_signup"]);
//管理员修改参赛学生信息
Route::post('admin/rework', [\App\Http\Controllers\Wwjcontroller::class, "SxqreworkStudent"]);
//管理员删除学生信息
Route::delete('admin/delete',[\App\Http\Controllers\Wwjcontroller  ::class, "SxqAdminDelete"]);

