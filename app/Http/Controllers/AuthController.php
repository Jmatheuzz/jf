<?php

namespace App\Http\Controllers;

use App\Mail\PasswordChangedMail;
use App\Mail\PasswordResetMail;
use App\Mail\VerifyEmailMail;
use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->responseWithError($validator->errors(), 'The given data was invalid.', 422);
        }
        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return $this->responseWithError([], 'Unauthorized', 401);
        }

        $user = Auth::user();
        return $this->responseWithSuccess([
            'role' => $user['role'], 
            'token' => $token, 
            'token_type' => 'bearer',
            'userId' => $user['id'],
            'name' => $user['name']
        ], 'Login Successfully');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= random_int(0, 9);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'CLIENTE',
            'code' => $code,
        ]);

        Mail::to($user['email'])->send(new VerifyEmailMail($user['name'], $code, 5));

        return $this->responseWithSuccess([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ], 'User created successfully');
    }

    public function validateEmail(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:6|min:6',
            'email' => 'required|string|email|max:255'
        ]);

        $user = User::where('email', $request->email)->where('code', $request->code)->first();
        
        if (!$user) {
            return $this->responseWithError([], 'Invalid code or email', 401);
        }
        
        $user->update(['code' => null, 'email_verified_at' => now()]);

        $token = Auth::login($user);
        return $this->responseWithSuccess([
            'name' => $user['name'],
            'role' => $user['role'], 
            'token' => $token, 
            'token_type' => 'bearer',
            'userId' => $user['id']
        ], 'User validate successfully');
    }

public function requestPasswordReset(Request $request)
{
    $request->validate([
        'email' => 'required|string|email|exists:users,email'
    ]);

    $user = User::where('email', $request->email)->first();

    // Gerar código de 6 dígitos
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= random_int(0, 9);
    }

    $user->update(['code' => $code]);

    Mail::to($user->email)->send(new PasswordResetMail($user->name, $code));

    return $this->responseWithSuccess([], 'Código de recuperação enviado para seu e-mail.');
}

public function validatePasswordResetCode(Request $request)
{
    $request->validate([
        'email' => 'required|string|email',
        'code' => 'required|string|max:6|min:6'
    ]);

    $user = User::where('email', $request->email)->where('code', $request->code)->first();

    if (!$user) {
        return $this->responseWithError([], 'Código inválido ou expirado', 400);
    }

    return $this->responseWithSuccess([], 'Código validado com sucesso');
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|string|email|exists:users,email',
        'code' => 'required|string|max:6',
        'new_password' => 'required|string|min:6|confirmed'
    ]);

    $user = User::where('email', $request->email)
        ->where('code', $request->code)
        ->first();

    if (!$user) {
        return $this->responseWithError([], 'Código inválido', 400);
    }

    $user->update([
        'password' => Hash::make($request->new_password),
        'code' => null
    ]);

    Mail::to($user->email)->send(new PasswordChangedMail($user->name));

    return $this->responseWithSuccess([], 'Senha redefinida com sucesso.');
}


    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return $this->responseWithSuccess(['user' => auth()->user()], 'User info!');
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();
        return $this->responseWithSuccess([], 'Successfully logged out');
    }

    /**
     * Get the token array structure.
     *
     * @param string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token)
    {
        return $this->responseWithSuccess(['user' => auth()->user(), 'token' => $token, 'token_type' => 'bearer'], 'Token generated');
        // 'expires_in' => auth()->factory()->getTTL() * 60,
    }
}
