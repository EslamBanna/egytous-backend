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
            if ($request->tags != null) {
                $tags = $request->tags;
                $tags = explode(',', $tags);
                foreach ($tags as $tag) {
                    PostTags::create([
                        'post_id' => $post->id,
                        'tag' => $tag
                    ]);
                }
            }
            if ($request->images && count($request->images) > 0) {
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

    public function getPosts()
    {
        try {
            $posts = Post::with(
                'user:id,name,image',
                'Tags:id,post_id,tag',
                'Images:id,post_id,image',
                'Comments',
                'Reacts:post_id,user_id,react',
                'Author:id,name'
            )->where('publish_at', '<=', now())
                ->orderBy('publish_at', 'desc')
                ->get();
            return $this->returnData('posts', $posts);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function getPostImages($id)
    {
        try {
            $check_if_post_exist_or_not = Post::find($id);
            if (!$check_if_post_exist_or_not || $check_if_post_exist_or_not->publish_at > now()) {
                return $this->returnError('E001', 'post not found');
            }
            $images = PostImages::where('post_id', $id)->get();
            return $this->returnData('images', $images);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function getPost($id)
    {
        try {
            $post = Post::with(
                'user:id,name,image',
                'Tags:id,post_id,tag',
                'Images:id,post_id,image',
                'Comments',
                'Reacts:post_id,user_id,react',
                'Author:id,name'
            )
                ->find($id);
            if (!$post || $post->publish_at > now()) {
                return $this->returnError('E001', 'post not found');
            }
            return $this->returnData('post', $post);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
