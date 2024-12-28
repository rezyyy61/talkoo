<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

class UserProfileController extends Controller
{
    public function getUser(Request $request)
    {
        $user = $request->user()->load('profile');
        return response()->json(['user' => $user]);
    }
}
