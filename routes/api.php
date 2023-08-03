<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthenticationController::class, 'login']);
Route::post('/register', [AuthenticationController::class, 'signUp']);
Route::post('/forgot-password', [AuthenticationController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthenticationController::class, 'resetPassword']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/me', [AuthenticationController::class, 'me']);
    Route::get('/active-people', [UserController::class, 'activePeople']);
    ###############  posts ###############################
    Route::post('/add-post', [PostController::class, 'addPost']);
    Route::get('/get-posts', [PostController::class, 'getPosts']);
    Route::get('/get-post/{id}', [PostController::class, 'getPost']);
    Route::get('/get-post-images/{id}', [PostController::class, 'getPostImages']);
    ######################################################
    ################ comments ############################
    Route::post('/add-comment', [CommentController::class, 'addComment']);
    Route::get('/get-comments/{post_id}', [CommentController::class, 'getComments']);
    Route::put('/update-comment/{comment_id}', [CommentController::class, 'updateComment']);
    Route::delete('/delete-comment/{comment_id}', [CommentController::class, 'deleteComment']);

    ######################################################
    Route::post('/add-story', [StoryController::class, 'addStory']);
    Route::get('/get-stories', [StoryController::class, 'getStories']);
    Route::get('/logout', [AuthenticationController::class, 'logout']);
});
