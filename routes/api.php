<?php
declare(strict_types=1);

use App\Http\Controllers\Api\GithubController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['auth:api'], static function () {
    Route::get('/github/users', [GithubController::class, 'users'])
        ->middleware([
            'request.github-user.params',
            'request.github-user.name-count'
        ]);
});
