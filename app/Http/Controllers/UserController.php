<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
class UserController extends Controller
{
    use GeneralTrait;
    public function activePeople(){
        try{
            $users = User::select('id', 'name', 'image')->take(5)->get();
            return $this->returnData('users', $users);
        }catch(\Exception $e){
            return $this->returnError('E001', 'Sorry, Something went wrong');
        }
    }
}
