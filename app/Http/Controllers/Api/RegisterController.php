<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class RegisterController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
            }

            $user           = new User();
            $user->password = Hash::make($request->password);
            $user->email    = $request->email;
            $user->name     = $request->name;
            $user->save();

            $token = $user->createToken('apiAuthToken');

            return response()->json(
                [
                    'message' => 'registered',
                    'data' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'token' => $token->plainTextToken,
                        'tokenType' => 'Bearer',
                        'createdAt' => $user->created_at
                    ],
                    'status' => 'OK'
                ]
            );
        } catch (Throwable $e) {
            $errorMessage = sprintf('Class: RegisterController; Method: register; Message %s', $e->getMessage());
            Log::error($errorMessage);

            return response()->json(
                [
                    'message' => 'Register error, please contact your system administrator',
                    'statusCode' => Response::HTTP_INTERNAL_SERVER_ERROR,
                ]
            );
        }
    }
}
