<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BlockListController;
use App\Http\Controllers\ChatRoomController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostReactController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\StoryViewersController;
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
    Route::post('/share-post/{id}', [PostController::class, 'sharePost']);
    Route::get('/get-posts', [PostController::class, 'getPosts']);
    Route::get('/get-posts-by-tag/{tag}', [PostController::class, 'getPostsByTag']);
    Route::get('/get-post/{id}', [PostController::class, 'getPost']);
    Route::get('/get-user-posts/{user_id}', [PostController::class, 'getUserPosts']);
    Route::get('/get-post-images/{id}', [PostController::class, 'getPostImages']);
    Route::post('/update-post/{id}', [PostController::class, 'updatePost']);
    Route::delete('/delete-post/{id}', [PostController::class, 'deletePost']);
    Route::get('/get-user-images/{userId}', [PostController::class, 'getUserImages']);
    ######################################################
    ################ comments ############################
    Route::post('/add-comment', [CommentController::class, 'addComment']);
    Route::get('/get-comments/{post_id}', [CommentController::class, 'getComments']);
    Route::post('/update-comment/{comment_id}', [CommentController::class, 'updateComment']);
    Route::delete('/delete-comment/{comment_id}', [CommentController::class, 'deleteComment']);
    ######################################################
    ################# save post ##########################
    Route::post('/save-post', [PostController::class, 'savePost']);
    Route::get('/get-saved-posts', [PostController::class, 'getSavedPosts']);
    ######################################################
    ################# story ##########################
    Route::post('/add-story', [StoryController::class, 'addStory']);
    Route::get('/get-stories', [StoryController::class, 'getStories']);
    Route::post('/show-story/{story_id}', [StoryViewersController::class, 'showStory']);
    ######################################################
    ################# React At post ######################
    Route::post('/react-at-post/{post_id}', [PostReactController::class, 'reactAtPost']);
    ######################################################
    ################# chat ###############################
    Route::post('/add-chat-room/{friend_id}', [ChatRoomController::class, 'addChatRoom']);
    Route::get('/get-chat-rooms', [ChatRoomController::class, 'getChatRooms']);
    Route::get('/get-chat-messages/{chat_room_id}', [ChatRoomController::class, 'getChatMessages']);
    Route::post('/send-message/{chat_room_id}', [ChatRoomController::class, 'sendMessage']);
    ######################################################
    ################# Block Lis ###############################
    Route::post('/block-user/{friend_id}', [BlockListController::class, 'blockUser']);
    ######################################################
    Route::get('/logout', [AuthenticationController::class, 'logout']);

});
