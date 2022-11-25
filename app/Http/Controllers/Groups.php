<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\{UserUpdateRequest,UserAddRequest};
use Spatie\Permission\Models\Role;
use App;
use Auth;
use App\Models\Group;
use App\Models\GroupMember;

class Groups extends Home_Controller
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
                        \App\Models\GroupMember::create(['member_id' => request('member_id')[$key], 'group_id' => $id, 'join_date' => date("Y-m-d", strtotime(request('join_date'))), 'role_id' => 4]);
                    }else{
                        \App\Models\GroupMember::where($array)->update(['member_id' => request('member_id')[$key], 'group_id' => $id, 'join_date' => date("Y-m-d", strtotime(request('join_date')))]);
                    }
                }
            }
        }
            return redirect()->back()->with('success', 'Congraturation New Members Added Successful' );
       }
       $this->data['positions'] = \App\Models\LeaderRole::get();
       $this->data['members'] = $members = GroupMember::where('group_id', $id)->get();
       $this->data['leaders'] = count($members) > 0 ? \App\Models\GroupLeader::whereIn('group_member_id', GroupMember::where('group_id', $id)->get(['id']))->get() : '';
       $this->data['is_leader'] = \App\Models\GroupLeader::whereIn('group_member_id', GroupMember::where('member_id', Auth::User()->id)->where('group_id', $id)->get(['id']))->first();
       $this->data['group'] = Group::find($id);
       return view('groups.members', $this->data);
    }

    public function edit()
    { 
        $id = request()->segment(3);
        if($_POST){
            Group::where('id', $id)->update(request()->except('_token'));
            return redirect('groups/members/'.$id)->with('success', 'New Group '. request('name') . ' Updated Successful' );
       }
        $this->data['group'] = Group::where('id', $id)->first();
        return view('groups.edit', $this->data);
    }

        public function notices()
        {
            $id = request()->segment(3); 
            $this->data['members'] = GroupMember::where('group_id', $id)->get();
            $this->data['group'] = Group::find($id);
            return view('groups.players', $this->data);
        }
        
        public function joinGroup()
        {
            if($_POST){
                $id = request('member_id');
                //dd(request()->all());
                if ((int)$id > 0 && request('group_id')) {
                    $groups = request('group_id');
                    foreach ($groups as $key => $value) {
                        if (request('group_id')[$key] != '') {
                            $array = ['group_id' => request('group_id')[$key], 'member_id' => $id];
                            $check_unique = GroupMember::where($array)->first();
                            if (empty($check_unique)) {
                                \App\Models\GroupMember::create(['group_id' => request('group_id')[$key], 'member_id' => $id, 'join_date' => date("Y-m-d"), 'role_id' => 4]);
                            }else{
                                \App\Models\GroupMember::where($array)->update(['group_id' => request('group_id')[$key], 'member_id' => $id, 'join_date' => date("Y-m-d")]);
                            }
                        }
                    }
                
                    return redirect()->back()->with('success', 'Congraturation You Successfully Joined '.number_to_words(count($groups)) . ' Group' );
               }else{
                return redirect()->back()->with('error', 'Something is Not Right Please Try again!!');
               }
            }
        }

    public function addLeader(){
    if($_POST){
        $id = request('group_member_id');
        if ((int)$id > 0 && request('role_id')) {
    
            $array = ['role_id' => request('role_id'), 'group_member_id' => $id];
            $check_unique = \App\Models\GroupLeader::where($array)->first();
            if (empty($check_unique)) {
                \App\Models\GroupLeader::create(['group_member_id' => $id, 'start_date' => request('start_date'),  'end_date' => request('end_date'), 'role_id' => request('role_id')]);
            }else{
                \App\Models\GroupLeader::where($array)->update(['start_date' => request('start_date'),  'end_date' => request('end_date'), 'role_id' => request('role_id')]);
            }
            \App\Models\GroupMember::where('id', $id)->update(['role_id' => request('role_id')]);
        }
            return redirect()->back()->with('success', 'Congraturation New Leader Added Successful' );
        }else{
            return redirect()->back()->with('error', 'Something is Not Right Please Try again!!');
        }
    }


    public function leader()
    {
        $id = request()->segment(3);
        $this->data['members'] = GroupMember::where('group_id', $id)->get();
        $this->data['group'] = Group::find($id);
        return view('groups.leaders', $this->data);
    }
    
    public function load_leader()
    {
     //   $users = \App\Models\User::whereIn('id', \App\Models\GroupMember::where('group_id', request('group_id'))->get(['member_id']))->get();
        $users = \App\Models\GroupLeader::whereIn('group_member_id', \App\Models\GroupMember::where('group_id', request('group_id'))->get(['id']))->get();

        $i =1;
        $comment ='';
        $date = request('date');
          
      $comment .= '<ul class="list-unstyled user-progress list-unstyled-border list-unstyled-noborder">';
 
            $i =1;
            if(count($users) > 0){
            foreach($users as $leader){
              
          $comment .= '<li class="media">
            <img alt="Kanisa Link" class="mr-3 rounded-circle" width="50" src="'.asset('public/assets/img/avatar/avatar-1.png') .'">
            <div class="media-body">
              <div class="media-title">'.$leader->member->user->name.'</div>
              <div class="text-job text-muted">Members: '.number_to_words($leader->member->count()).' </div>
              <div class="text-job">Role: '.$leader->role->name.' </div>
            </div>
            <div class="media-progressbar">
            <div class="progress-text">Start Date - <u>'.valid_date($leader->start_date).'</u> </div>
            <div class="progress-text">End Date - <u>'.valid_date($leader->end_date).'</u></div>
                Time Left: <b>'.timeAgo($leader->end_date).'</b>
            </div>
            <div class="media-cta">
            <a href="'.url('users/leader_profile/'.$leader->member->member_id).'" class="btn btn-outline-primary"> Profile </a>
            <a onclick="send_message('.$leader->member->user->id.')" id="user_id_'.$leader->member->user->id.'" class="btn btn-outline-success"> Message</a>
            </div>
          </li>';
         }
        }else{
            $comment .= '<div class="text-center p-3 text-muted">
                <h5>No Results Found</h5>
                <p>Looks like you have not added any <b>group leader</b> yet!</p>
            </div>';
         }
           
        echo $comment; 
    }

    public function zones(){
        $this->data['groups'] = \App\Models\Zone::count();
        $this->data['zones'] = \App\Models\Zone::where('status', 1)->get();
        $this->data['leaders'] = \App\Models\ZoneLeader::whereIn('user_id', \App\Models\Zone::get(['user_id']))->orderBy('id', 'ASC')->get();
        $this->data['positions'] = \App\Models\LeaderRole::get();
        $this->data['members']  = \App\Models\User::orderBy('id', 'ASC')->get();
        return view('groups.zones.index', $this->data);
    }
 
    public function viewZone(){
        $id = request()->segment(3);
        $this->data['zone'] = \App\Models\Zone::where('id', $id)->first();
        $this->data['sections'] = \App\Models\Section::where('zone_id', $id)->get();
        return view('groups.zones.view', $this->data);
    }

    public function myZone(){
        $user = \App\Models\SectionMember::where('member_id', Auth::User()->id)->first();
        if(!empty($user)){
            $this->data['zone'] = \App\Models\Zone::where('id', $user->section->zone_id)->first();
            $this->data['is_leader'] = \App\Models\ZoneLeader::where('user_id', Auth::User()->id)->first();
            $this->data['sections'] = \App\Models\Section::where('zone_id', $user->section->zone_id)->get();
            return view('groups.zones.my_zone', $this->data);
        }else{
            return redirect()->back()->with('error', 'You are not added to any zone. Please Contact You Zone Leader.');
        }
    }

    
    public function mygroups(){
        $this->data['groups'] = \App\Models\GroupMember::where('member_id', Auth::User()->id)->get();
        $id = request()->segment(3);
        if(count($this->data['groups'])){
            $this->data['positions'] = \App\Models\LeaderRole::get();
            if((int)$id > 0){
            $this->data['members'] = $members = GroupMember::where('group_id', $id)->get();
            $this->data['leaders'] = count($members) > 0 ? \App\Models\GroupLeader::whereIn('group_member_id', GroupMember::where('group_id', $id)->get(['id']))->get() : '';
            $this->data['is_leader'] = \App\Models\GroupLeader::whereIn('group_member_id', GroupMember::where('member_id', Auth::User()->id)->where('group_id', $id)->get(['id']))->first();
            $this->data['this_group'] = Group::find($id);
            }
            return view('groups.my_members', $this->data);
        }else{
            return redirect()->back()->with('error', 'You are not added to any zone. Please Contact You Zone Leader.');
        }
    }


    public function addZoneLeader(){
        if($_POST){
            $id = request('user_id');
          //  dd(request()->all());
          if ((int)$id > 0 && request('role_id')) {
        
                $array = ['role_id' => request('role_id'), 'zone_id' => request('zone_id'),  'user_id' => $id];
                $check_unique = \App\Models\ZoneLeader::where($array)->first();
                if (empty($check_unique)) {
                    \App\Models\ZoneLeader::create(['user_id' => $id, 'zone_id' => request('zone_id'), 'start_date' => request('start_date'),  'end_date' => request('end_date'), 'role_id' => request('role_id')]);
                }else{
                    \App\Models\ZoneLeader::where($array)->update(['start_date' => request('start_date'),  'end_date' => request('end_date'), 'role_id' => request('role_id')]);
                    $section = \App\Models\ZoneLeader::where('zone_id', request('zone_id'))->first();
                    if(!empty($section)){
                        $member = \App\Models\SectionMember::create([
                        'join_date' => request('start_date'),
                        'role_id' => request('role_id'),
                        'member_id' => request('user_id'),
                        'section_id' => $section->id,
                        'status' => 1]);
                    }
                }
                return redirect()->back()->with('success', 'Congraturation New Leader Added Successful' );
            }
            }else{
                return redirect()->back()->with('error', 'Something is Not Right Please Try again!!');
            }
        }

    public function addZone(){
        if($_POST){
            $data = request()->all();
          //  dd($data);
            $name = request('name');
            $data = [
                'name' => request('name'),
                'location' => request('location'),
                'user_id' => request('user_id'),
                'status' => 1
            ];

            $check =  \App\Models\Zone::where('name', $name)->first();
            if(empty($check)){
               $zone = \App\Models\Zone::create($data);
               $section = \App\Models\Section::create([
                   'name' => request('name'), 'location' => request('location'), 'zone_id' => $zone->id, 'status' => 1
                   ]);
                
                   $member = \App\Models\SectionMember::create([
                        'join_date' => request('start_date'),
                        'role_id' => request('role_id'),
                        'member_id' => request('user_id'),
                        'section_id' => $section->id,
                        'status' => 1
                    ]);
                
                    \App\Models\SectionLeader::create([
                        'start_date' => request('start_date'),
                        'end_date' => request('start_date'),
                        'section_member_id' => $member->id,
                        'role_id' => request('role_id'),
                        'status' => 1
                    ]);
    
                $zoneleader =  \App\Models\ZoneLeader::create([
                    'start_date' => request('start_date'),
                    'end_date' => request('end_date'),
                    'zone_id' => $zone->id,
                    'role_id' => request('role_id'),
                    'user_id' => request('user_id'),
                    'status' => 1
                ]);
                return redirect('groups/viewZone/'.$zone->id)->with('success', 'Congraturation '.$name.' Zone Added Successful' );
            }else{
                return redirect()->back()->with('error', 'Zone With Same name('.$name.') Already Exists. Please Try again!!');
            }
        }
    }


    public function addSection(){
        if($_POST){
            $data = request()->all();
          //  dd($data);
            $name = request('name');
            $data = [
                'name' => request('name'),
                'location' => request('location'),
                'zone_id' => request('zone_id'),
                'status' => 1
            ];
            
            $check =  \App\Models\Section::where('name', $name)->where('zone_id', request('zone_id'))->first();
            if(empty($check)){
               $section = \App\Models\Section::create($data);
               $section_array = [
                'join_date' => request('start_date'),
                'role_id' => request('role_id'),
                'member_id' => request('user_id'),
                'section_id' => $section->id,
                'status' => 1
            ];

            $member = \App\Models\SectionMember::create($section_array);
                $leader_array = [
                    'start_date' => request('start_date'),
                    'end_date' => request('start_date'),
                    'section_member_id' => $member->id,
                    'role_id' => request('role_id'),
                    'status' => 1
                    ];
                \App\Models\SectionLeader::create($leader_array);

                return redirect('groups/ZoneMembers/'.$section->zone_id.'/'.$section->id)->with('success', 'Congraturation '.$name.' Section Added Successful' );
            }else{
                return redirect()->back()->with('error', 'Section With Same name('.$name.') Already Exists. Please Try again!!');
            }
        }
    }

    
    public function addCell(){
        if($_POST){
            //$data = request()->all();
          //  dd($data);
            $name = request('name');
            $data = [
                'name' => request('name'),
                'location' => request('location'),
                'section_id' => request('section_id'),
                'status' => 1
            ];
            
            $check =  \App\Models\Cells::where('name', $name)->where('section_id', request('section_id'))->first();
            if(empty($check)){
               $section = \App\Models\Cells::create($data);
               $section_array = [
                'join_date' => request('start_date'),
                'role_id' => request('role_id'),
                'member_id' => request('user_id'),
                'cell_id' => $section->id,
                'status' => 1
            ];

            $member = \App\Models\CellMember::create($section_array);
                $leader_array = [
                    'start_date' => request('start_date'),
                    'end_date' => request('start_date'),
                    'cell_member_id' => $member->id,
                    'role_id' => request('role_id'),
                    'status' => 1
                    ];
                \App\Models\CellLeader::create($leader_array);

                return redirect()->back()->with('success', 'Congraturation '.$name.' Cell Added Successful' );
            }else{
                return redirect()->back()->with('error', 'Cell With Same name('.$name.') Already Exists. Please Try again!!');
            }
        }
    }

    
    public function viewSection(){
        $id = request()->segment(3);
        $this->data['section'] = \App\Models\Section::where('id', $id)->first();
        $members =\App\Models\SectionMember::where('section_id', $id);
        $this->data['section_members'] = \App\Models\CellMember::whereIn('cell_id',  \App\Models\Cells::where('section_id', $id)->get(['id']))->get();
        $this->data['leaders'] =  \App\Models\SectionLeader::whereIn('section_member_id', $members->get(['id']))->get();
        $this->data['sections'] = \App\Models\Cells::where('section_id', $id)->get();
        return view('groups.zones.view_section', $this->data);
    }

    public function ZoneMembers(){
        $zone = request()->segment(3);
        $section = request()->segment(4);
        $cell = request()->segment(5);
        if((int) $cell > 0){
            $this->data['type'] = 'Cell';
            $this->data['group'] = $group = \App\Models\Cells::where('id', $cell)->first();
            $this->data['section_members'] = \App\Models\CellMember::where('cell_id',  $cell)->get();
            $this->data['leaders'] =  \App\Models\CellLeader::whereIn('cell_member_id', $group->members()->get(['id']))->get();
        }elseif((int) $section > 0 && (int) $zone > 0) {
            $this->data['type'] = 'Section';
            $this->data['group'] = \App\Models\Section::where('id', $section)->first();
            $this->data['section_members'] = \App\Models\SectionMember::where('section_id', $section)->get();
            //\App\Models\CellMember::whereIn('cell_id',  \App\Models\Cells::where('section_id', $section)->get(['id']))->get();
            $this->data['leaders'] =  \App\Models\SectionLeader::whereIn('section_member_id',\App\Models\SectionMember::where('section_id', $section)->get(['id']))->get();
        }else{
            $this->data['type'] = 'Zone';
            $this->data['group'] = \App\Models\Zone::where('id', $zone)->first();
            $this->data['section_members'] =  \App\Models\SectionMember::whereIn('section_id',  \App\Models\Section::where('zone_id', $zone)->get(['id']))->get();
            //\App\Models\CellMember::whereIn('cell_id',  \App\Models\Cells::whereIn('section_id', \App\Models\Section::where('zone_id', $zone)->get(['id']))->get(['id']))->get();
            $this->data['leaders'] =  \App\Models\ZoneLeader::where('zone_id', $zone)->get();
        }
        $this->data['sections']  = \App\Models\Section::where('zone_id', $zone)->get();
        return view('groups.zones.members', $this->data);
    }
    
    public function loadCell() {
        $section_id = request('section_id');
        $cells =  \App\Models\Cells::where('section_id', $section_id)->get();
       // dd($cells);
        $option = '<option></option>';
        foreach ($cells as $slot) {

            $option .= '<option value="' . $slot->id . '">' . $slot->name . ' - ' . $slot->location . '</option>';
        }
        echo $option;
    }


    public function addCellMember(){
        if($_POST){
            $id = request('user_id');
            $section = request()->except(['cell_id', '_token']);
         //   $cell = request()->except(['section_id', '_token']);
            $members= request('member_id');
            foreach ($members as $key => $value) {
                if (request('member_id')[$key] != '') {
                    $array = ['member_id' => request('member_id')[$key]];
                    $check_unique = \App\Models\SectionMember::where($array)->first();
                    $check = \App\Models\CellMember::where($array)->first();
                    
                /* Add Cell Member
                    if (empty($check) && request('cell_id')) {
                        \App\Models\CellMember::create(['member_id' => request('member_id')[$key], 'cell_id' => request('cell_id'), 'join_date' => date("Y-m-d", strtotime(request('join_date'))), 'role_id' => request('role_id')]);
                    }else{
                        \App\Models\CellMember::where($array)->update(['member_id' => request('member_id')[$key], 'cell_id' => request('cell_id'), 'join_date' => date("Y-m-d", strtotime(request('join_date')))]);
                    } 
                */
                    // Add Section Member
                    if (empty($check_unique) && request('section_id')) {
                        \App\Models\SectionMember::create(['member_id' => request('member_id')[$key], 'section_id' => request('section_id'), 'join_date' => date("Y-m-d", strtotime(request('join_date'))), 'role_id' => request('role_id')]);
                    }else{
                        \App\Models\SectionMember::where($array)->update(['member_id' => request('member_id')[$key], 'section_id' => request('section_id'), 'join_date' => date("Y-m-d", strtotime(request('join_date')))]);
                    }
                }
            }
            return redirect()->back()->with('success', 'Congraturation New ' .count($members). ' Members Added..' );
        }else{
                return redirect()->back()->with('error', 'Something is Not Right Please Try again!!');
            }
        }
    
        public function remove(){
            $type = request()->segment(3);
            $id = request()->segment(4);
            if($type == 'zone' && (int)$id > 0){
                \App\Models\SectionMember::where('id', $id)->delete();
             
                return redirect()->back()->with('success', 'Member Removed...' );
            }else if($type == 'group' && (int)$id > 0){
                GroupMember::where('id', $id)->delete();
                return redirect()->back()->with('success', 'Member Removed...' );
            }
            return redirect()->back()->with('error', 'Something is Not Right Please Try again!!');
        }
    
}
