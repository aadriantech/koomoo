<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

final class DistanceController extends Controller
{
    public function hamming(Request $request): ?JsonResponse
    {
        try {

            $validator = Validator::make($request->all(), [
                'x' => 'required|numeric|min:0',
                'y' => 'required|numeric|max:1073741824'
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
            }

            echo nl2br("Input: x = $request->x, y = $request->y" . PHP_EOL);
            echo nl2br("Hamming Distance: " . gmp_hamdist($request->x, $request->y) . PHP_EOL);

            echo nl2br("Explanation: " . PHP_EOL);
            echo nl2br("$request->x (" . decbin((int)$request->x) . ")" . PHP_EOL);
            echo nl2br("$request->y (" . decbin((int)$request->y) . ")" . PHP_EOL);

            return null;
        } catch (Throwable $e) {
            echo 'Bad request';
            Log::error($e->getMessage());
        }

        return null;
    }
}
