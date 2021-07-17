<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\GithubUserService;
use Closure;
use Illuminate\Http\Request;

final class CheckGithubUserRequestParams
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $raw = $request->getContent();
        $content = json_decode($raw, true);

        if (empty($content['usernames'])) {
            return response()->json(
              [
                  'message' => 'Missing required parameter: usernames',
                  'data' => null,
                  'status' => GithubUserService::STATUS_INVALID
              ]
            );
        }

        return $next($request);
    }
}
