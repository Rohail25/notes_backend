<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where('role', 'user')
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        return response()->json($users);
    }
}
