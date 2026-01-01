<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;

/*
|--------------------------------------------------------------------------
| AuthController
|--------------------------------------------------------------------------
| This controller handles all authentication-related actions for the API.
| Functions include register, login, logout, refresh token, and profile retrieval.
| It extends BaseController to use standard API response methods.
*/
class AuthController extends BaseController
{
    /**
     * Register a new user
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        // Validate request data
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password', // Confirm password must match
        ]);

        // If validation fails, return error
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Hash password and create user
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $success['user'] = $user;

        // Return success response
        return $this->sendResponse($success, 'User register successfully.');
    }

    /**
     * Login user and return JWT token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        // Get credentials from request
        $credentials = request(['email', 'password']);

        // Attempt to authenticate and generate token
        if (!$token = auth()->attempt($credentials)) {
            return $this->sendError('Unauthorised.', ['error' => 'Unauthorised']);
        }

        // Return token in response
        $success = $this->respondWithToken($token);

        return $this->sendResponse($success, 'User login successfully.');
    }

    /**
     * Get authenticated user profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        // Retrieve authenticated user
        $success = auth()->user();

        return $this->sendResponse($success, 'User profile retrieved successfully.');
    }

    /**
     * Logout user (invalidate token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout(); // Invalidate JWT token

        return $this->sendResponse([], 'Successfully logged out.');
    }

    /**
     * Refresh JWT token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $success = $this->respondWithToken(auth()->refresh());

        return $this->sendResponse($success, 'Refresh token returned successfully.');
    }

    /**
     * Format JWT token response
     *
     * @param string $token
     * @return array
     */
    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,               // The JWT token
            'token_type'   => 'bearer',             // Token type
            'expires_in'   => auth()->factory()->getTTL() * 60 // Expiration time in seconds
        ];
    }
}
