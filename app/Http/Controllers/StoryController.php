<?php

namespace App\Http\Controllers;

use App\Models\Story;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
    use GeneralTrait;
    public function addStory(Request $request)
    {
        try {
            $validate =  Validator::make($request->all(), [
                'title' => 'string|max:100',
                'description' => 'string',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
            ]);
            if ($validate->fails()) {
                return $this->returnError('E001', $validate->errors());
            }
            $story_image = '';
            if ($request->hasFile('image')) {
                $story_image = $this->saveImage($request->image, 'stories');
            }
            Story::create([
                'title' => $request->title,
                'description' => $request->description,
                'image' => $story_image,
                'user_id' => auth()->user()->id,
            ]);
            return $this->returnSuccessMessage('Story added successfully');
        } catch (\Exception $e) {
            return $this->returnError('E001', 'Sorry, Something went wrong');
        }
    }

    public function getStories()
    {
        try {
            $stories = Story::with('user:id,name,image')
                ->orderBy('created_at', 'desc')
                ->whereDoesntHave('viewers', function ($query) {
                    $query->where('user_id', auth()->user()->id);
                })
                ->get();
            return $this->returnData('stories', $stories);
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
