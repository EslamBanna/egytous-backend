<?php

namespace App\Http\Controllers;

use App\Models\Story;
use App\Models\StoryViewers;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class StoryViewersController extends Controller
{
    use GeneralTrait;
    public function showStory($story_id)
    {
        try {
            $story = Story::find($story_id);
            if (!$story) {
                return $this->returnError('E001', 'Story not found');
            }
            $check_if_user_show_the_story_before = StoryViewers::where('user_id', auth()->user()->id)
                ->where('story_id', $story_id)
                ->first();
            if (!$check_if_user_show_the_story_before) {
                StoryViewers::create([
                    'user_id' => auth()->user()->id,
                    'story_id' => $story_id,
                ]);
            }
            return $this->returnSuccessMessage('Story viewed successfully');
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
