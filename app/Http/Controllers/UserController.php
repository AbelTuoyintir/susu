<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //add user
    public function addUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required',
            'phoneOne' => 'nullable',
            'password' => 'required|min:6',
            'role' => 'required',
            'image' => 'nullable',
            'status' => 'nullable', 
            'country'=>'nullable',
            'city'=>'nullable',
            'state'=>'nullable',
            'zip'=>'nullable',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        do {
            $memberId = mt_rand(100000, 999999);
        } while (User::where('member_id', $memberId)->exists());
        $validated['member_id'] = $memberId;

        $user = User::create($validated);
        return redirect()->route('users')->with('success', 'User added successfully');
                   
    }

    public function index(){
        $users = User::all();
        return view('livewire.pages.admin.users', compact('users'));
    }

    public function edit($userId){
        $user = User::find($userId);
        return view('livewire.pages.admin.users', compact('user'));
    }

    public function update(Request $request, $userId){
        $user = User::find($userId);
        $user->update($request->all());
        return redirect()->route('users')->with('success', 'User updated successfully');
    }

    public function delete($userId){
        $user = User::findOrFail($userId);
        $user->update(['status' => 'inactive']);
        return redirect()->route('users')->with('success', 'User deactivated successfully');
    }
}
