<?php

namespace App\Http\Controllers\web;

use App\Models\AddUser;
use App\Models\MasterClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserControllerWeb
{
    public function add_user()
    {
        $mc = MasterClient::with('user')->find(auth()->user()->id);

        $users = $mc ? $mc->user : collect(); // ensures $users is a collection

        $user_limit = AddUser::where('mc_id', $mc->id)->count();

        return view('web.user.list', compact('users', 'user_limit'));
    }

    // public function post_update(Request $request)
    // {
    //     // Validate the input

    //     $request->validate([
    //         'name'          => 'required',
    //         'role'          => 'required',
    //         'mobile_number' => 'required',
    //         'password'      => 'required',
    //         'permission'    => 'required',
    //         'status'        => 'required',
    //     ]);

    //     $mcId = auth()->user()->id;

    //     $permissions = is_array($request->permission)
    //         ? implode(',', $request->permission)
    //         : $request->permission;

    //     // Prepare data
    //     $data = AddUser::insert([
    //         'mc_id'         => $mcId,
    //         'name'          => $request->name,
    //         'role'          => $request->role,
    //         'mobile_number' => $request->mobile_number,
    //         'password'      => Hash::make($request->password),
    //         'status'        => $request->status,
    //         'permission'    => $request->permission ?? null, // if you have permission checkboxes
    //     ]);


    //     return back()->with([
    //         'status' => 'Success',
    //         'message' => 'User added successfully'
    //     ]);
    // }

    public function post_update(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'role'          => 'required',
            'mobile_number' => 'required',
            'password'      => 'required',
            'permission'    => 'required',
            'status'        => 'required',
        ]);

        $mcId = auth()->user()->id;

        // Make sure permission is always an array
        $permissions = is_array($request->permission)
            ? implode(',', $request->permission)
            : $request->permission;

        AddUser::create([
            'mc_id'         => $mcId,
            'name'          => $request->name,
            'role'          => $request->role,
            'mobile_number' => $request->mobile_number,
            'password'      => Hash::make($request->password),
            'status'        => $request->status,
            'permission'    => $permissions,
        ]);

        return back()->with([
            'status' => 'Success',
            'message' => 'User added successfully'
        ]);
    }


    public function updateUser(Request $r)
    {
        $r->validate([
            'name' => 'required',
            'mobile_number' => 'required|digits:10',
            'role' => 'required',
            'status' => 'required',
        ]);

        $permissions = $r->permission ? implode(',', $r->permission) : '';

        AddUser::where('id', $r->id)->update([
            'name' => $r->name,
            'mobile_number' => $r->mobile_number,
            'role' => $r->role,
            'permission' => $permissions,
            'status' => $r->status
        ]);

        return back()->with('success', 'User updated successfully');
    }
}
