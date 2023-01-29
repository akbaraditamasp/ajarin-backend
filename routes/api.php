<?php

use App\Http\Controllers\Course;
use App\Http\Controllers\User;
use App\Http\Controllers\Vendor;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//User route group
Route::controller(User::class)->prefix("/user")->group(function () {
    Route::get("/login", "login");
    Route::middleware("auth:sanctum")->delete("/logout", "logout");
    Route::post("/", "create");
});

//Vendor route group
Route::controller(Vendor::class)->prefix("/vendor")->group(function () {
    Route::middleware("auth:sanctum")->post("/join/{code}", "join");
    Route::middleware("auth:sanctum")->post("/{id}/code", "createInvitationCode");
    Route::middleware("auth:sanctum")->delete("/{id}/revoke-code", "revokeInvitationCode");
    Route::middleware("auth:sanctum")->post("/", "create");
});

//Course route group
Route::controller(Course::class)->prefix("/course")->group(function () {
    Route::middleware("auth:sanctum")->post("/", "create");
});
