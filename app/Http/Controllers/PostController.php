<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostImages;
use App\Models\PostTags;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    use GeneralTrait;

    public function addPost(Request $request)
    {
        try {
            DB::beginTransaction();
            $validtor = Validator::make($request->all(), [
                'title' => 'string|required',
                'description' => 'string',
                'author_id' => 'string|required',
                'publish_at' => 'date|required',
            ]);
            if ($validtor->fails()) {
                return $this->returnError('E001', $validtor->errors()->first());
            }
            $post = Post::create([
                'title' => $request->title,
                'description' => $request->description,
                'user_id' => auth()->user()->id,
                'author_id' => $request->author_id,
                'publish_at' => $request->publish_at
            ]);
            if (count($request->tags) > 0) {
                foreach ($request->tags as $tag) {
                    PostTags::create([
                        'post_id' => $post->id,
                        'tag' => $tag
                    ]);
                }
            }
            if (count($request->images) > 0) {
                $post_image = '';
                foreach ($request->images as $image) {
                    $post_image = $this->saveImage($image, 'posts');
                    PostImages::create([
                        'post_id' => $post->id,
                        'image' => $post_image
                    ]);
                    $post_image = '';
                }
            }
            DB::commit();
            return $this->returnSuccessMessage('post added successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function getPost()
    {
        try {
            $posts = Post::with(
                'user:id,name,image',
                'Tags:id,post_id,tag',
                'Images:id,post_id,image',
                'Comments',
                'Reacts:post_id,user_id,react',
                'Author:id,name'
            )->get();
            return $this->returnData('posts', $posts);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
