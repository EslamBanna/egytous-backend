<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReact;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostReactController extends Controller
{
    use GeneralTrait;
    public function reactAtPost(Request $request, $post_id)
    {
        try {
            DB::beginTransaction();
            $check_the_post_is_exist = Post::find($post_id);
            if (!$check_the_post_is_exist) {
                return $this->returnError('E002', 'the post is not exist');
            }
            $check_if_reacted_before_or_not = PostReact::where('user_id', auth()->user()->id)
                ->where('post_id', $post_id)->first();
            if (!$check_if_reacted_before_or_not) {
                PostReact::create([
                    'user_id' => auth()->user()->id,
                    'post_id' => $post_id,
                    'react_type' => $request->react_type
                ]);
            }
            DB::commit();
            return $this->returnSuccessMessage('Reacted Successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
