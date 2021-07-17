<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GithubUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GithubController extends Controller
{
    /**
     * @param GithubUserService $githubUserService Dependency Injection (Singleton)
     */
    public function __construct(private GithubUserService $githubUserService)
    {
    }

    public function users(Request $request): JsonResponse
    {
        $data = $this->githubUserService
            ->setRequest($request)
            ->getUsersBasicInfo();

        return response()->json(
            [
                'message' => $this->githubUserService->message,
                'data' => $data,
                'status' => GithubUserService::STATUS_OK
            ]
        );
    }
}
