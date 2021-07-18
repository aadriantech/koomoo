<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

final class LoginController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            $credentials = request(['email', 'password']);

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status_code' => Response::HTTP_UNAUTHORIZED,
                    'message' => 'Unauthorized'
                ]);
            }

            $user = User::where('email', $request->email)->first();
            $user->tokens()->delete();
            $token = $user->createToken('apiAuthToken')->plainTextToken;

            return response()->json(
                [
                    'statusCode' => Response::HTTP_OK,
                    'accessToken' => $token,
                    'tokenType' => 'Bearer',
                ]
            );

        } catch (Throwable $e) {
            $errorMessage = sprintf('Class: LoginController; Method: login; Message %s', $e->getMessage());
            Log::error($errorMessage);

            return response()->json(
                [
                    'message' => 'Login error, please contact your system administrator',
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR,
                ]
            );
        }
    }
}
