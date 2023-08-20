<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostImages;
use App\Models\PostTags;
use App\Models\SavePost;
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
    public function sharePost($post_id)
    {
        try {
            DB::beginTransaction();
            $check_the_post_exist = Post::find($post_id);
            if (!$check_the_post_exist) {
                return $this->returnError('E002', 'The Post Is Not Exist');
            }
            $shared_post =  Post::create([
                'title' => $check_the_post_exist->title,
                'description' => $check_the_post_exist->description,
                'user_id' => auth()->user()->id,
                'author_id' => $check_the_post_exist->user_id,
                'publish_at' => now(),
                'privacy'  => 'public'
            ]);
            $post_tags = $check_the_post_exist->Tags;
            if ($post_tags != null) {
                foreach ($post_tags as $tag) {
                    PostTags::create([
                        'post_id' => $shared_post->id,
                        'tag' => $tag->tag
                    ]);
                }
            }
            $post_images = $check_the_post_exist->Images;
            if ($post_images != null) {
                $link_len = strlen((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/images/posts/');
                foreach ($post_images as $image) {
                    $attach_name = substr($image->image, $link_len);
                    PostImages::create([
                        'post_id' => $shared_post->id,
                        'image' => $attach_name
                    ]);
                }
            }
            DB::commit();
            return $this->returnSuccessMessage('The Post Is Shared');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError('E001', $e->getMessage());
        }
    }
    public function getPosts()
    {
        try {
            $blocked_list = auth()->user()->blocked_user->pluck('blocked_user_id')->toArray();
            $blocked_list_2 = auth()->user()->block_user->pluck('user_id')->toArray();
            $blocked_list = array_merge($blocked_list, $blocked_list_2);
            $posts = Post::with(
                'user:id,name,image',
                'Tags:id,post_id,tag',
                'Images:id,post_id,image',
                'Author:id,name',
                'ISavedPostBefore',
            )
                ->whereNotIn('user_id', $blocked_list)
                ->withCount('Reacts')
                ->withCount('IReacted as i_reacted')
                ->where('publish_at', '<=', now())
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
                'Author:id,name',
                'ISavedPostBefore'
            )
                ->withCount('Reacts')
                ->withCount('IReacted as i_reacted')
                ->find($id);
            if (!$post || $post->publish_at > now()) {
                return $this->returnError('E001', 'post not found');
            }
            return $this->returnData('post', $post);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function savePost(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|exists:posts,id'
            ]);
            if ($validator->fails()) {
                return $this->returnError('E001', $validator->errors()->first());
            }
            $check_if_post_saved_before_or_not = Post::find($request->post_id)->ISavedPostBefore;
            if ($check_if_post_saved_before_or_not == null) {
                SavePost::create([
                    'user_id' => auth()->user()->id,
                    'post_id' => $request->post_id
                ]);
            } else {
                $check_if_post_saved_before_or_not->delete();
            }
            return $this->returnSuccessMessage('post saved successfully');
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }
    public function getSavedPosts()
    {
        try {
            $saved_posts = SavePost::with([
                'post.user:id,name,image',
                'post.Tags:id,post_id,tag',
                'post.Images:id,post_id,image',
                // 'post.Reacts',
                'post.Author:id,name',
            ])
                ->withCount('Reacts')
                ->withCount('IReacted as i_reacted')
                ->where('user_id', auth()->user()->id)
                ->orderBy('created_at', 'desc')
                ->get();
            return $this->returnData('saved_posts', $saved_posts);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function updatePost(Request $request, $post_id)
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
            $check_if_post_exist_or_not = Post::find($post_id);
            if (!$check_if_post_exist_or_not) {
                return $this->returnError('E002', 'The Post Is not Exist');
            }
            if ($check_if_post_exist_or_not->user_id != auth()->user()->id) {
                return $this->returnError('E003', 'You Can not update this post');
            }
            // $check_if_post_exist_or_not->delete();
            $check_if_post_exist_or_not->update([
                'title' => $request->title,
                'description' => $request->description,
                'publish_at' => $request->publish_at
            ]);
            PostTags::where('post_id', $post_id)->delete();
            if ($request->tags != null) {
                $tags = $request->tags;
                $tags = explode(',', $tags);
                foreach ($tags as $tag) {
                    PostTags::create([
                        'post_id' => $check_if_post_exist_or_not->id,
                        'tag' => $tag
                    ]);
                }
            }
            if (count($request->removed_images) > 0) {
                foreach ($request->removed_images as $image_id) {
                    $check_the_image = PostImages::find($image_id);
                    if (!$check_the_image) {
                        return $this->returnError('E002', 'The Image Is not Exist');
                    }
                    if ($check_the_image->post_id != $post_id) {
                        return $this->returnError('E003', 'You Can not delete this image');
                    }
                    $check_the_image->delete();
                }
            }
            if ($request->images && count($request->images) > 0) {
                $post_image = '';
                foreach ($request->images as $image) {
                    $post_image = $this->saveImage($image, 'posts');
                    PostImages::create([
                        'post_id' => $check_if_post_exist_or_not->id,
                        'image' => $post_image
                    ]);
                    $post_image = '';
                }
            }
            DB::commit();
            return $this->returnSuccessMessage('The Post Updated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError('E001', $e->getMessage());
        }
    }
    public function deletePost($post_id)
    {
        try {
            Db::beginTransaction();
            $check_the_post_exist = Post::find($post_id);
            if (!$check_the_post_exist) {
                return $this->returnError('E002', 'The Post Is not Exist');
            }
            if ($check_the_post_exist->user_id != auth()->user()->id) {
                return $this->returnError('E003', 'You Can not delete this post');
            }
            $check_the_post_exist->delete();
            DB::commit();
            return $this->returnSuccessMessage('The Post Deleted Successfully');
        } catch (\Exception $e) {
            Db::rollBack();
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
