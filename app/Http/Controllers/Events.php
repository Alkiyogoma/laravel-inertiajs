<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App;
use App\Models\Event;
use App\Models\EventGroup;
use App\Models\EventAttandance;

class Events extends Home_Controller
{

    /**
     * Display a listing of the resource.
     */

    public function index()
    { 
        //$this->authorize(User::class, 'index');
       if($_POST){
            Group::create(request()->all());
            return redirect()->back()->with('success', 'New Group '. request('name') . ' Added Successful' );
       }
        $this->data['roles'] = Group::get();
        return view('groups.index', $this->data);
    }

    public function members(){ 
        
        $id = request()->segment(3);
       if($_POST){
        if ((int)$id > 0 && request('member_id')) {
            $members = request('member_id');
            foreach ($members as $key => $value) {
                if (request('member_id')[$key] != '') {
                    $array = ['member_id' => request('member_id')[$key], 'group_id' => $id];
                    $check_unique = GroupMember::where($array)->first();
                    if (empty($check_unique)) {
                        \App\Models\GroupMember::create(['member_id' => request('member_id')[$key], 'group_id' => $id, 'join_date' => request('join_date'), 'role_id' => 4]);
                    }else{
                        \App\Models\GroupMember::where($array)->update(['member_id' => request('member_id')[$key], 'group_id' => $id, 'join_date' => request('join_date')]);
                    }
                }
            }
        }
            return redirect()->back()->with('success', 'Congraturation New Members Added Successful' );
       }
       $this->data['members'] = GroupMember::where('group_id', $id)->get();
       $this->data['group'] = Group::find($id);
       return view('groups.members', $this->data);
    }


     public function create()
        {
            return view('admin.users.create');
        }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if(!App::environment('demo') && !$user->isme)
        {
            $user->delete();
        } else
        {
            return response()->json(['message' => 'User accounts cannot be deleted in demo mode.'], 400);
        }
    }

    public function roles(Request $request)
    { 
        if($request->ajax())
        {
            $roles = Role::get();
            return response()->json($roles);
        }
        return view('admin.users.roles');
    }
        public function calendar()
        {
            $this->data['tasks'] = \App\Models\Event::get();
            return view('events.calendar', $this->data);
        }

        public function addCalendar()
        {
            if($_POST){
                    $data = request()->all();
                  //  dd($data);
                    \App\Models\Event::create($data);
                    return redirect()->back()->with('secondary', 'Congraturation New Contribution Received..' );
            }
        }
    
        public function mycalendar()
        {
            $this->data['events'] = \App\Models\Event::orderBy('id', 'DESC')->get();
            return view('events.my_calendar', $this->data);
        }

}
