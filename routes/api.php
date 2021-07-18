<?php
declare(strict_types=1);

use App\Http\Controllers\Api\DistanceController;
use App\Http\Controllers\Api\GithubController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/distance/hamming', [DistanceController::class, 'hamming']);


/*
|--------------------------------------------------------------------------
| Authenticated API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => 'auth:sanctum'], static function () {
    Route::get('/github/users', [GithubController::class, 'users'])
        ->middleware([
            'request.github-user.params',
            'request.github-user.name-count'
        ]);
});
