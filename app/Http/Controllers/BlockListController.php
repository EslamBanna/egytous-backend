<?php

namespace App\Http\Controllers;

use App\Models\BlockList;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class BlockListController extends Controller
{
    use GeneralTrait;

    public function blockUser($firend_id)
    {
        try {
            $check_if_user_blocked_before = BlockList::where('user_id', auth()->user()->id)
                ->where('blocked_user_id', $firend_id)
                ->first();
            if ($check_if_user_blocked_before) {
                return $this->returnError('E001', 'You blocked this user before');
            }
            if ($firend_id == auth()->user()->id) {
                return $this->returnError('E001', 'You can not block yourself');
            }
            BlockList::create([
                'user_id' => auth()->user()->id,
                'blocked_user_id' => $firend_id,
            ]);
            return $this->returnSuccessMessage('User blocked successfully');
        } catch (\Exception $e) {
            return $this->returnError('E001', $e->getMessage());
        }
    }
}
