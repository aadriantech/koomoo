<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\GithubUserService;
use Closure;
use Illuminate\Http\Request;

class CheckGithubUserRequestNameCount
{
    public const MAX_USERS = 10;

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

        if ((int)count($content['usernames']) > self::MAX_USERS) {
            return response()->json(
                [
                    'message' => sprintf('Requested number of usernames is out of range max: %s', self::MAX_USERS),
                    'data' => null,
                    'status' => GithubUserService::STATUS_INVALID
                ]
            );
        }

        return $next($request);
    }
}
