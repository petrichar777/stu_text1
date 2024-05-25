<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Admin;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class Wwjcontroller extends Controller
{
    public function register(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_name' => 'required|string|max:255',
            'student_id' => 'required|string|max:255|unique:students',
            'major' => 'required|string|max:255',
            'class' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        // 使用哈希加密密码
        $credentials = [
            'student_name' => $validatedData['student_name'],
            'student_id' => $validatedData['student_id'],
            'major'=> $validatedData['major'],
            'class' => $validatedData['class'],
            'password' => Hash::make($validatedData['password']), // 对密码进行哈希处理
        ];

        $dm = Student::create($credentials);
        if ($dm) {
            // 生成 token
            $token = JWTAuth::fromSubject($dm);
            return response()->json(['token' => $token], 201);
        } else {
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('student_id', 'password');

        // 验证提供的用户名和密码
        $user = Student::where('student_id', $credentials['student_id'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            // 验证密码是否匹配
            $token = JWTAuth::fromSubject($user);
            return response()->json([
                'message' => 'Login successful',
                'token' => $token
            ]);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    public function adminLogin(Request $request)
    {
        $validatedData = $request->validate([
            'admin_name' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('admin_name', $validatedData['admin_name'])->first();

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        if ($admin->password === $validatedData['password']) {
            return response()->json([
                'message' => 'Admin login successful',
            ]);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'Successfully logged out']);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Failed to log out, please try again'], 500);
        }
    }

    //管理员查询单个学生信息
    public function selectonestu(Request $request)
    {

        $id = $request['student_id'];

        // 根据学号或姓名查询学生报名信息
        $registrations = Registration::where('student_id', $id)

            ->get();

        // 检查是否找到了学生报名信息
        if ($registrations->isEmpty()) {
            return response()->json(['message' => '该学生未报名任何比赛'], 404);
        }
        return $registrations;
        // 提取学生信息
        $studentInfo = [];
        foreach ($registrations as $registration) {
            $studentInfo[] = [
                'student_id' => $registration->student_id,
                'student_name' => $registration->student_name,
                'event_name' => $registration->event_name,
                'class' => $registration->class,
                'major' => $registration->major,
            ];
        }
        var_dump($studentInfo);
        // 返回学生信息
        return response()->json($studentInfo, 200);
    }

    public function selectStudent(Request $request): \Illuminate\Http\JsonResponse
    {
        $id = $request['student_id'];
        $student = Student::where('student_id', $id);
        var_dump($id);
        if (!$student) {
            return response()->json(['Success' => false, 'message' => '学生不存在'], 404);
        }

        // 查询学生报名的所有项目
        $registrations = Registration::where('student_id', $id)->get();

        // 计算每个项目的报名人数
        $signupCounts = Registration::select('event_name')
            ->selectRaw('count(*) as signup_count')
            ->groupBy('event_name')
            ->pluck('signup_count', 'event_name');

        // 将报名人数保存到 events 表中的 num_stu 字段
        foreach ($signupCounts as $event_name => $signup_count) {
            Event::where('event_name', $event_name)->update(['num_stu' => $signup_count]);
        }

        // 准备返回的学生信息
        $studentInfo = $registrations->map(function ($registration) use ($signupCounts) {
            return [
                'event_name' => $registration->event_name,
                'student_id' => $registration->student_id,
                'student_name' => $registration->student_name,
                'major' => $registration->major,
                'class' => $registration->class,
                'signup_count' => $signupCounts[$registration->event_name] ?? 0, // 获取该项目的报名人数
            ];
        });

        return response()->json([
            'Success' => true,
            'Message' => '查询成功',
            'Student_Info' => $studentInfo
        ], 200);
    }


    public function selectAdmin(Request $request): \Illuminate\Http\JsonResponse
    {
        $id = $request['student_id'];

        $student = Student::where('student_id', $id)->first();

        if (!$student) {
            return response()->json(['message' => '查询失败！该学生不存在'], 404);
        }

        // 查询学生报名的所有项目
        $registrations = Registration::where('student_id', $id)->get();

        // 计算每个项目的报名人数
        $signupCounts = Registration::select('event_name')
            ->selectRaw('count(*) as signup_count')
            ->groupBy('event_name')
            ->pluck('signup_count', 'event_name');

        // 将报名人数保存到 events 表中的 num_stu 字段
        foreach ($signupCounts as $event_name => $signup_count) {
            Event::where('event_name', $event_name)->update(['num_stu' => $signup_count]);
        }

        // 准备返回的学生信息
        $studentInfo = $registrations->map(function ($registration) use ($signupCounts) {
            return [
                'event_name' => $registration->event_name,
                'student_id' => $registration->student_id,
                'student_name' => $registration->student_name,
                'major' => $registration->major,
                'class' => $registration->class,
                'signup_count' => $signupCounts[$registration->event_name] ?? 0, // 获取该项目的报名人数
            ];
        });

        return response()->json([
            'Success' => true,
            'Message' => '查询成功',
            'Student_Info' => $studentInfo
        ], 200);
    }

    public function SxqAdminDelete(Request $request): JsonResponse
    {
        // 获取请求参数
        $studentId = $request->input('student_id');

        // 检查学生是否存在
        $student = Student::find($studentId);
        if (!$student) {
            return response()->json(['message' => '删除失败！该学生不存在'], 404);
        }

        // 尝试删除学生
        try {
            $student->delete();
            return response()->json(['success' => true, 'message' => '删除成功！'], 200);
        } catch (\Exception $e) {
            // 如果删除失败，返回错误信息
            return response()->json(['success' => false, 'message' => '删除失败！发生了一些错误'], 500);
        }
    }

    public function SxqstudentDelete(Request $request): JsonResponse
    {
        // 获取请求参数
        $studentId = $request->input('student_id');
        $studentName = $request->input('student_name');
        $class = $request->input('class');
        $major = $request->input('major');

        // 检查学生是否存在
        $student = Student::where('student_id', $studentId)
            ->where('student_name', $studentName)
            ->where('class', $class)
            ->where('major', $major)
            ->first();

        if (!$student) {
            return response()->json(['message' => '删除失败！该学生不存在'], 404);
        }

        // 尝试删除学生
        try {
            $student->delete();
            return response()->json(['success' => true, 'message' => '删除成功！'], 200);
        } catch (\Exception $e) {
            // 如果删除失败，返回错误信息
            return response()->json(['success' => false, 'message' => '删除失败！发生了一些错误'], 500);
        }
    }
    //管理员修改学生信息
    public function SxqreworkStudent(Request $request)
    {
        $id = $request['student_id'];
        $name = $request['student_name'];
        $class = $request['class'];
        $major = $request['major'];
        $event_name = $request['event_name'];
        // 根据当前学号和姓名查找学生
        $student = Registration::where('student_id', $id)
            ->get();
        if (!$student) {
            return response()->json(['message' => '无该学生信息'], 404);
        }

        // 更新学生信息
        $student->student_id = $id;
        $student->student_name = $name;
        $student->class = $class;
        $student->major = $major;
        $student->event_name = $event_name;

        if ($student->save()) {
            return response()->json(['message' => '学生信息修改成功'], 200);
        } else {
            return response()->json(['message' => '学生信息修改失败'], 500);
        }
    }




    public function student_signup(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'student_id' => 'required|integer',
            'student_name' => 'required|string',
            'class' => 'required|string',
            'major' => 'required|string',
            'event_name' => 'required|string',
        ]);

        $studentId = $validatedData['student_id'];

        // 检查该学生已经报名的项目数量
        $projectsCount = Registration::where('student_id', $studentId)->count();

        if ($projectsCount >= 4) {
            $success = false;
            $message = "每个学生只能报名不超过4个项目";
        } else {
            // 进行报名操作，可以将报名信息存储到数据库中
            Registration::create([
                'student_id' => $validatedData['student_id'],
                'student_name' => $validatedData['student_name'],
                'class' => $validatedData['class'],
                'major' => $validatedData['major'],
                'event_name' => $validatedData['event_name'],
            ]);

            $success = true;
            $message = "报名成功";
        }

        return response()->json([
            'Success' => $success,
            'Message' => $message
        ]);
    }

    public function Admin_signup(Request $request)
    {
        // 验证请求数据
        $validatedData = $request->validate([
            'student_id' => 'required|integer',
            'student_name' => 'required|string',
            'class' => 'required|string',
            'major' => 'required|string',
            'event_name' => 'required|string',
        ]);

        // 创建新的学生记录
        $student = new Registration();
        $student->student_id = $validatedData['student_id'];
        $student->student_name = $validatedData['student_name'];
        $student->class = $validatedData['class'];
        $student->major = $validatedData['major'];
        $student->event_name = $validatedData['event_name'];

        // 保存学生记录并返回响应
        if ($student->save()) {
            return response()->json(['Success' => true, 'Message' => '学生添加成功']);
        } else {
            return response()->json(['Success' => false, 'Message' => '学生添加失败']);
        }
    }

}
