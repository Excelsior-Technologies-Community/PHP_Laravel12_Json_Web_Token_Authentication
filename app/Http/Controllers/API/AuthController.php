<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        return $this->sendResponse(['user' => $user], 'User registered successfully.');
    }

    public function login(Request $request)
    {
        $key = 'login-attempts:' . $request->ip() . '|' . $request->email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->sendError('Too many login attempts.', ['retry_after' => $seconds . ' seconds'], 429);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            RateLimiter::hit($key, 600);
            return $this->sendError('Unauthorised', ['error' => 'Invalid credentials']);
        }

        RateLimiter::clear($key);

        $this->recordDeviceActivity($request, $token);

        return $this->sendResponse($this->respondWithToken($token), 'User logged in successfully.');
    }

    public function logout()
    {
        auth()->logout(true);
        return $this->sendResponse([], 'Logged out successfully.');
    }

    public function refresh()
    {
        return $this->sendResponse($this->respondWithToken(auth()->refresh()), 'Token refreshed successfully.');
    }

    public function profile()
    {
        return $this->sendResponse(auth()->user(), 'User profile retrieved successfully.');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return $this->sendResponse($user, 'Profile updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return $this->sendError('Current password is incorrect');
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return $this->sendResponse([], 'Password changed successfully.');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(60);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        return $this->sendResponse(['token' => $token], 'Password reset token generated.');
    }

    public function getActiveDevices()
    {
        $devices = DB::table('user_devices')
            ->where('user_id', auth()->id())
            ->get(['id', 'ip_address', 'user_agent', 'created_at']);

        return $this->sendResponse($devices, 'Active devices retrieved.');
    }

    public function logoutDevice($id)
    {
        DB::table('user_devices')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return $this->sendResponse([], 'Device session removed.');
    }

    protected function recordDeviceActivity($request, $token)
    {
        DB::table('user_devices')->insert([
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'token_id' => hash('sha256', $token),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }
}