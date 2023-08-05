<?php

namespace App\Http\Controllers;

use App\Models\CommentImage;
use App\Models\Post;
use App\Models\PostComments;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    use GeneralTrait;

    public function addComment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'post_id' => 'required|exists:posts,id',
                'comment' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->returnError('E001', $validator->errors()->first());
            }
            $comment = PostComments::create([
                'post_id' => $request->post_id,
                'user_id' => auth()->user()->id,
                'comment' => $request->comment,
            ]);
            if ($request->hasFile('image')) {
                $image_name = $this->saveImage($request->image, 'comments');
                CommentImage::create([
                    'comment_id' => $comment->id,
                    'image' => $image_name
                ]);
            }
            return $this->returnSuccessMessage('comment added successfully');
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function getComments($post_id)
    {
        try {
            $check_if_post_exist_or_not = Post::find($post_id);
            if (!$check_if_post_exist_or_not) {
                return $this->returnError('E001', 'post not found');
            }
            $comments = PostComments::where('post_id', $post_id)->with('commentImages:id,comment_id,image', 'user:id,name,image')->get();
            return $this->returnData('comments', $comments);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function updateComment(Request $request, $comment_id)
    {
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'comment' => 'required',
            ]);
            if ($validator->fails()) {
                return $this->returnError('E001', $validator->errors()->first());
            }
            $post_comment = PostComments::find($comment_id);
            if (!$post_comment) {
                return $this->returnError('E001', 'comment not found');
            }
            if ($post_comment->user_id != auth()->user()->id) {
                return $this->returnError('E001', 'you can not update this comment');
            }
            if($request->comment != '' || $request->comment != null){
                $post_comment->update([
                    'comment' => $request->comment
                ]);
            }
            if($request->delete_old_image == true){
                $post_comment->commentImages()->delete();
            }
            if($request->hasFile('image') && $request->image != null ){
                // return $this->returnError('E001', 'you can not update image');
                $image_name = $this->saveImage($request->image, 'comments');
                $post_comment->commentImages()->updateOrCreate([
                    'image' => $image_name
                ]);
                // return $image_name;
            }
            DB::commit();
            return $this->returnSuccessMessage('comment updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError('E001', $e->getMessage());
        }
    }

    public function deleteComment($comment_id)
    {
        try {
            $post_comment = PostComments::find($comment_id);
            if (!$post_comment) {
                return $this->returnError('E001', 'comment not found');
            }
            if ($post_comment->user_id != auth()->user()->id) {
                return $this->returnError('E001', 'you can not delete this comment');
            }
            $post_comment->delete();
            return $this->returnSuccessMessage('comment deleted successfully');
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
