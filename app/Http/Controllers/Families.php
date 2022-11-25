<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\{UserUpdateRequest,UserAddRequest};
use Spatie\Permission\Models\Role;
use App;
use Auth;
use App\Models\User;
use App\Models\Family;
use App\Models\Relation;
use App\Models\FamilyMember;
use DB;

class Families extends Home_Controller
{

    /**
     * Display a listing of the resource.
     */

    public function index()
    { 
        //$this->authorize(User::class, 'index');
       if($_POST){
           $user = User::find(request('user_id'));
           $family = Family::create(array_merge(request()->all(), ['name' => $user->name]));
           if($family){
                FamilyMember::create(['family_id' => $family->id,'status' => 1, 'user_id' => $user->id, 'relation_id' => $family->relation_id]);
           }
           return redirect()->back()->with('success', 'New Family of '. $user->name . ' Added Successful' );
       }
        $this->data['families'] = Family::get();
        return view('admin.family.index', $this->data);
    }

    public function members(){
        
        $id = request()->segment(3);
       if($_POST){
        if ((int)$id > 0 && request('user_id')) {
            $members = request('user_id');
            foreach ($members as $key => $value) {
                if (request('user_id')[$key] != '') {
                    $array = ['user_id' => request('user_id')[$key], 'group_id' => $id];
                    $check_unique = GroupMember::where($array)->first();
                    if (empty($check_unique)) {
                        \App\Models\GroupMember::create(['user_id' => request('user_id')[$key], 'group_id' => $id, 'join_date' => request('join_date'), 'role_id' => 4]);
                    }else{
                        \App\Models\GroupMember::where($array)->update(['user_id' => request('user_id')[$key], 'group_id' => $id, 'join_date' => request('join_date')]);
                    }
                }
            }
        }elseif (request('name') && request('sex') && request('user_id')) {
            # code...
        }
            return redirect()->back()->with('success', 'Congraturation New Members Added Successful' );
       }
       $this->data['members'] = GroupMember::where('group_id', $id)->get();
       $this->data['group'] = Group::find($id);
       return view('groups.members', $this->data);
    }


    public function addMember(){

       if($_POST){
        $id = request('family_id');
        //dd(request()->all());
        if ($id && request('name') !='' && request('sex') !='' && request('relation_ids') !='') {
            $family = Family::where('id', $id)->first();
            $phone = request('phone') !='' ? validate_phone_number(trim(request('phone')))[1] : $family->user->phone;
           
            $user_data = [
                'name' => request('name'),
                'sex' => request('sex'),
                'dob' => request('dob'),
                'jod' => request('jod'),
                'martial' => "single",
                'phone' => $phone,
                'email' => random_username(request('name')).'@albolink.com',
                'password' => bcrypt(random_username(request('name')).'@albolink.com'),
                'zone' => $family->user->zone,
                'address' => $family->user->address,
                'baptized' => request('baptized'),
                'role_id' => request('role_id')
            ];
            $added_user = User::create($user_data);
            FamilyMember::create(['family_id' => $id,'status' => 1, 'user_id' => $added_user->id, 'relation_id' => request('relation_ids')]);
            return redirect()->back()->with('error', 'Congraturation '.$added_user->name.' Added to your Family member' );

        }elseif((int)$id > 0 && request('user_id')) {

            $family = FamilyMember::where('family_id', $id)->where('user_id', request('user_id'))->first();
            if (empty($family)) {
                FamilyMember::create(['family_id' => $id,'status' => 1, 'user_id' => request('user_id'), 'relation_id' => request('relation_id')]);
            }else{
                \App\Models\GroupMember::where('family_id', $id)->where('user_id', request('user_id'))->update(['user_id' => request('user_id'), 'family_id' => $id, 'relation_id' => request('relation_id')]);
            }
            return redirect()->back()->with('success', 'Congraturation New Family Member Added Successful' );
       }else{
        return redirect()->back()->with('error', 'Something Went Wrong Please try Again!!!');
    }
    }else{
        return redirect()->back()->with('error', 'Something Went Wrong Please try Again!!!');
    }
       
}


    public function selfRegister($id)
    {
        $id = request()->segment(3);
       if($id){
        $user = User::find($id);
        if(strtolower($user->sex) == 'female'){
            $relation = Relation::where('name', 'Mother')->first()->id;
        }else{
            $relation = Relation::where('name', 'Father')->first()->id;
        }
        $family = Family::create(['name' => $user->name, 'user_id' => $id ,'status' => 1, 'relation_id' => $relation]);
        if($family){
             FamilyMember::create(['family_id' => $family->id,'status' => 1, 'user_id' => $user->id, 'relation_id' => $family->relation_id]);
        }
            return redirect()->back()->with('success', 'Congraturation Your Family Registerd Successful' );
       }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        if(!App::environment('demo'))
        {
            $user->update($request->only([
                'name', 'email'
            ]));

            if($request->password)
            {
                $user->update(['password' => Hash::make($request->password)]);
            }

            if($request->role && $request->user()->can('edit-users') && !$user->isme)
            {
                $role = Role::find($request->role);
                if($role)
                {
                    $user->syncRoles([$role]);
                }
            }
        }
        return response()->json($user);
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


    public function profile()
    {
    
        $id = request()->segment(3);
        if($id == ''){
            $id = Auth::User()->id;
        }
        $this->data['my_family'] = $family = \App\Models\Family::where('user_id', $id)->first();
        if($family){
                $this->data['families'] =  \App\Models\FamilyMember::where('family_id', $family->id)->get();
                $this->data['relations'] = \App\Models\Relation::get();
                $this->data['listofmembers'] = \App\Models\User::whereNotIn('id',  \App\Models\FamilyMember::get(['user_id']))->get();
                return view('admin.family.profile', $this->data);
        }else{
            return redirect()->back()->with('success', 'Congraturation Your Comment Received..' );
        }
    }
      


    public function newbeliever()
    { 
        
        if($_POST){
          //  dd(request()->all());
            $data = request()->except(['phone', 'email']);
            $phone = request('phone') !='' ? validate_phone_number(trim(request('phone')))[1] : '';
            $email = request('email') == '' ? random_username(request('name')).'@albolink.com' : request('email');
            $user =  \App\Models\Believer::create(array_merge($data, ['password' => bcrypt(request('phone')), 'phone' => $phone, 'email' => $email, 'about' => 'New User', 'status' => 1]));
        }    
        $week = date('Y-m-d', strtotime('-7 days'));
        $month = (int)date('m');
       
        $this->data['this_year'] = \App\Models\Believer::whereYear('created_at', date('Y'))->count();
        $this->data['this_month'] = \App\Models\Believer::whereYear('created_at', date('Y'))->whereMonth('created_at', $month)->count();
        $this->data['this_week'] = \App\Models\Believer::where('created_at', '>=', $week)->count();
        $this->data['users'] = \App\Models\Believer::get();
        $this->data['works'] = \App\Models\WorkStatus::get();
        $this->data["datas"] = DB::SELECT("SELECT count(id) as ynumber, EXTRACT(MONTH FROM t.created_at) as month FROM believers t WHERE EXTRACT(YEAR FROM t.created_at)= date_part('year', CURRENT_DATE)  GROUP BY EXTRACT(MONTH FROM t.created_at) ORDER BY EXTRACT(MONTH FROM t.created_at) ASC");
        $this->data["title"] = "Church New Believers Trend";
        return view('admin.family.believer', $this->data);
    }

    public function addBeliever(){
         
        if($_POST){
            //  dd(request()->all());
              $data = request()->except(['phone', 'email']);
              $phone = request('phone') !='' ? validate_phone_number(trim(request('phone')))[1] : '';
              $email = request('email') == '' ? random_username(request('name')).'@albolink.com' : request('email');
              $user =  \App\Models\Believer::create(array_merge($data, ['password' => bcrypt(request('phone')), 'phone' => $phone, 'email' => $email, 'about' => 'New User', 'status' => 1]));
              return redirect('families/newbeliever')->with('success', 'All New Members Uploaded Successfully!');
            } 
            $this->data['users'] = \App\Models\Believer::get();
            $this->data['works'] = \App\Models\WorkStatus::get();
            return view('admin.family.add_believer', $this->data);
        }

    public function believer_report()
    {
        $type = request()->segment(3);
        $id = request()->segment(4);
        if($id != '' && $type != ''){
            if($type == 'week'){
                $number = $id * -7;
                $week = "'".date('Y-m-d', strtotime($number. ' days'))."'";
              //  dd($week);
                $this->data['users'] = \App\Models\Believer::whereYear('created_at', date('Y'))->whereDate('created_at', '>', $week)->get();
                $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM believers t WHERE YEAR(t.created_at)=YEAR(CURDATE()) AND created_at>'.$week.'  GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
                $this->data["title"] = "Church believers for Last ". number_to_words($id) .' Weeks.' ;
            }elseif($type == 'month'){
                $this->data['users'] = \App\Models\Believer::whereYear('created_at', date('Y'))->whereMonth('created_at', (int)$id)->get();
                $this->data["datas"] = DB::SELECT("SELECT CONCAT(YEAR(created_at), '/', WEEK(created_at)) as month,count(*) as ynumber FROM believers t WHERE YEAR(t.created_at)=YEAR(CURDATE())  AND MONTH(t.created_at)=".(int)$id." GROUP BY CONCAT(YEAR(created_at), '/', WEEK(created_at)) ORDER  BY CONCAT(YEAR(created_at), '/', WEEK(created_at)) ASC");
                //DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM believers t WHERE YEAR(t.created_at)=YEAR(CURDATE())  GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
                $this->data["title"] = "Church believers Trend on " .\DateTime::createFromFormat('!m', $id)->format('F');;
            }elseif($type == 'location'){
                $address = "'".$id."'";
                $this->data['users'] = \App\Models\Believer::where('address', $id)->get();
                $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM believers t WHERE YEAR(t.created_at)=YEAR(CURDATE()) and address = '.$address.' GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
                $this->data["title"] = "Church believers from ". $id;
            }else{
                $this->data['users'] = [];
                $this->data["datas"] = [];
                $this->data["title"] = "Church believers Trend";   
            }
        }else{
            $this->data['users'] = \App\Models\Believer::get();
            $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM believers t WHERE YEAR(t.created_at)=YEAR(CURDATE())  GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
            $this->data["title"] = "Church believers Trend";
        }
       
        $this->data['locations'] = DB::select('SELECT distinct address, count(address) AS total FROM believers group by address order by count(address) desc');   
        return view('admin.family.believer_report', $this->data);
    }
    
    public function myfamily()
    {

       $user =  \App\Models\FamilyMember::where('user_id', Auth::User()->id)->first();

        if(!empty($user)){
            $this->data['my_family'] = $family = \App\Models\Family::where('id', $user->family_id)->first();
            $this->data['families'] =  \App\Models\FamilyMember::where('family_id', $family->id)->get();
                $this->data['relations'] = \App\Models\Relation::get();
                $this->data['listofmembers'] = \App\Models\User::whereNotIn('id',  \App\Models\FamilyMember::get(['user_id']))->get();
                return view('admin.family.user_profile', $this->data);
        }else{
            $this->selfRegister(Auth::User()->id);
        }
}



}
