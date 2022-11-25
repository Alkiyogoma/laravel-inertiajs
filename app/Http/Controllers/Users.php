<?php

namespace App\Http\Controllers;

use App\Imports\UsersImport;
use App\Imports\VisitorImport;
use App\Exports\UsersExport;
use App\Exports\VisitorExport;
use App\Imports\BeliverImport;
use App\Imports\ChurchUpload;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Church;
use Illuminate\Support\Facades\Hash;
use App;
use Auth;
use DB;
use Image;

class Users extends Home_Controller
{
  
    public function index()
    { 
    
        can_access('manage-users');
      
        $this->data['users'] = User::where('status', 1)->get();
        $this->data['groups'] = \App\Models\Group::get();
        $this->data['age_group'] = []; // DB::select("SELECT CASE WHEN (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d'))) <= 18 THEN '1-18' WHEN (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d'))) <= 28 THEN '19-25' WHEN (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d'))) <= 35 THEN '26-35' WHEN (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d'))) <= 60 THEN '36-60' WHEN (DATE_FORMAT(NOW(), '%Y') - DATE_FORMAT(dob, '%Y') - (DATE_FORMAT(NOW(), '00-%m-%d') < DATE_FORMAT(dob, '00-%m-%d'))) <= 60 THEN '60-100' END AS age, COUNT(*) total FROM users GROUP BY age");
        $this->data['student_by_class'] = []; // DB::table('age_group')->get();
        $this->data['zone_group'] = DB::SELECT("with totals as (select count(0) AS total, zone from " . church_name() . ".users group by zone), zone_male as (select count(0) AS male, zone from " . church_name() . ".users where lower(sex) = 'male' group by zone), 
        zone_female as (select count(0) AS female, zone AS zone from " . church_name() . ".users where lower(sex) = 'female' group by zone)
        select a.male AS male,a.zone,b.total AS total,c.female AS female from ((zone_male a join totals b on(b.zone = a.zone)) left join zone_female c on(c.zone = a.zone))");
        return view('admin.users.index', $this->data);
    }

    
    public function create()
    {   if($_POST){
        $data = request()->except(['phone', 'email','avatar']);
        $phone = request('phone') !='' ? validate_phone_number(trim(request('phone')))[1] : '';
        $email = request('email') == '' ? random_username(request('name')).'@albolink.com' : request('email');
        $filename = '';
        if (!empty(request('avatar'))) {
            $file = request()->file('avatar');
            $filename = time() . rand(10, 666) . '.' . $file->guessExtension();
           
            $image = Image::make(request()->file('avatar'));
            $image->orientate();
            $image->resize(152, 165)->save('storage/uploads/images/' . $filename);
        }
        $user = User::create(array_merge($data, ['password' => bcrypt(request('phone')), 'phone' => $phone, 'avatar' => $filename, 'email' => $email, 'status' => 1]));
        
        return redirect('users/profile/'.$user->id)->with('success', 'User ' . request('name') . ' created successfully');

    }
    $this->data['zones'] = \App\Models\Zone::get();
    $this->data['roles'] = \App\Models\Role::orderBy('id', 'DESC')->get();

    return view('admin.users.create', $this->data);
    }

    public function userUpload() 
    {
        Excel::import(new UsersImport, request()->file('user_file'));
        return redirect('/users')->with('success', 'All Members Uploaded Successfully!');
    }

    public function VisitorUpload() 
    {
        Excel::import(new VisitorImport, request()->file('user_file'));
        return redirect('users/visitors')->with('success', 'All Visitors Uploaded Successfully!');
    }

    public function BeliverUpload() 
    {
        Excel::import(new BeliverImport, request()->file('user_file'));
        return redirect('families/newbeliever')->with('success', 'All New Members Uploaded Successfully!');
    }
    
    public function clientUpload() 
    {
        Excel::import(new ChurchUpload, request()->file('client_file'));
        return redirect('dashboard/church')->with('success', 'All Church List Uploaded!');
    }



    /**
     * Store a newly created resource in storage.
     */
    public function resetPass()
    {
        $user = \App\Models\User::where('id', Auth::User()->id)->update(['email' => request('email'), 'password' =>  bcrypt(request('password'))]);
    
        return redirect('/logout')->with('success', 'Congraturation Your Password Updated Successful' );
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
    public function edit()
    { 
        $id = request()->segment(3);
        if($id != '' && can_access('manage-users')){
            $id = request()->segment(3);
        }else{
            $id = Auth::User()->id;
        }
        $user = \App\Models\User::find($id);
        if($_POST){
        $data = request()->except(['avatar','_token']);
        // dd($data);
         $filename = '';
         if (!empty(request('avatar'))) {
             $file = request()->file('avatar');
             $filename = time() . rand(10, 666) . '.' . $file->guessExtension();
            
             $image = Image::make(request()->file('avatar'));
             $image->orientate();
             $image->resize(152, 165)->save('storage/uploads/images/' . $filename);
             $user->where('id', $id)->update(array_merge($data, ['avatar' => $filename]));
            
            }else{
                $user->where('id', $id)->update($data);
            }
         return redirect('Users/profile/'.$id)->with('success', 'Congraturation '.$user->name.' Profile Update Successful' );
        }
        $this->data['user'] = $user;
        $this->data['zones'] = \App\Models\Zone::get();

        return view('admin.users.edit', $this->data);
    }


    protected function rules()
    {
        return [
            'phone' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ];
    }

    public function reset()
    {
        if($_POST){
            $data = request()->except(['_token','password']);
         $user =  \App\Models\User::where($data)->update(['password' =>  bcrypt(request('password'))]);
         $message = "Hello ". $user->name ." We've received a request to reset password for your KanisaLink Account. If you didn't make this request please reset your password now."; 
         send_sms(str_replace('+', '', validate_phone_number(trim($user->phone))[1]), $message);
        }
    }


    public function profile()
    { 
        $id = request()->segment(3);
        if($id == ''){
            $id = Auth::User()->id;
        }
        $this->data['profile'] = \App\Models\User::find($id);
        $this->data['la_family'] = $family = \App\Models\FamilyMember::where('user_id', $id)->first();
        $this->data['families'] = [];
        if(!empty($family)){
        $this->data['families'] =  \App\Models\FamilyMember::where('family_id', $family->family_id)->get();
        }
        $this->data['listofmembers'] = \App\Models\User::whereNotIn('id',  \App\Models\FamilyMember::get(['user_id']))->get();
        $this->data['givings'] = \App\Models\Collect::where('user_id', $id)->get();
        $this->data['groups'] = \App\Models\GroupMember::where('member_id', $id)->get();
        $this->data['messages'] = \App\Models\Message::where('user_id', $id)->latest()->get();
        $this->data['relations'] = \App\Models\Relation::get();

        return view('admin.users.user_profile', $this->data);
    }

    public function visitor_profile()
    { 
        $id = request()->segment(3);
        if($id == ''){
            $id = Auth::User()->id;
        }
        $this->data['profile'] = \App\Models\Visitor::find($id);
        return view('admin.users.new_visitor', $this->data);
    }

    public function leaders()
        {
        if($_POST){
            $id = request('user_id');
        if ((int)$id > 0 && request('name') !='' && request('sex') !='' && request('relation_ids') !='') {
            $family = Family::where('id', $id)->first();
            $phone = request('phone') !='' ? validate_phone_number(trim(request('phone')))[1] : $family->user->phone;
            $id = request('user_id');
           
            $user_data = [
                'name' => request('name'),
                'sex' => request('sex'),
                'dob' => request('dob'),
                'jod' => request('jod'),
                'martial' => "single",
                'phone' => $phone,
                'email' => request('email') !='' ? request('email') : random_username(request('name')).'@albolink.com',
                'password' => bcrypt(request('email') !='' ? request('email') : random_username(request('name')).'@albolink.com'),
                'zone' => request('zone'),
                'address' => request('address'),
                'baptized' => request('baptized'),
                'role_id' => request('role_id')
            ];
            $added_user = User::create($user_data);
            \App\Models\Leader::create(['user_id' => $added_user->id,'status' => 1, 'role_id' => request('role_id'), 'start_date' => date('Y-m-d'), 'end_date' => date("Y-m-d", strtotime('+1 year'))]);
            return redirect()->back()->with('error', 'Congraturation '.$added_user->name.' Added to your Family member' );
            }elseif((int)$id > 0 && request('user_id')) {

            $leader = \App\Models\Leader::where('user_id', $id)->first();
            if (empty($leader)) {
                \App\Models\Leader::create(['user_id' => $id,'status' => 1, 'role_id' => request('leader_id'), 'start_date' => request('start_date'), 'end_date' => request('end_date')]);
            }else{
                \App\Models\Leader::where('user_id', $id)->update(['status' => 1, 'role_id' => request('leader_id'), 'start_date' => request('start_date'), 'end_date' => request('end_date')]);
            }
            return redirect()->back()->with('success', 'Congraturation New Leader Added Successful' );
            }else{
                return redirect()->back()->with('error', 'Something Went Wrong Please try Again!!!');
            }
        }
        $this->data['listofmembers'] = \App\Models\User::whereNotIn('id',  \App\Models\Leader::get(['user_id']))->get();
        $this->data['roles'] = \App\Models\LeaderRole::get();
        $this->data['leaders'] = \App\Models\Leader::orderBy('role_id', 'ASC')->get();
            return view('admin.users.leaders', $this->data);
        }

        public function leader_profile()
          {
            $id = request()->segment(3);
            if($id == ''){
                $id = Auth::User()->id;
            }
            $this->data['profile'] = \App\Models\User::find($id);
            $this->data['leaders'] = \App\Models\Leader::where('user_id', $id)->get();         
            $this->data['groups'] = \App\Models\GroupLeader::whereIn('group_member_id', \App\Models\GroupMember::where('member_id', $id)->get(['id']))->get();
            $this->data['messages'] = \App\Models\Message::where('user_id', $id)->get();
            $this->data['relations'] = \App\Models\Relation::get();
            return view('admin.users.profile', $this->data);
        }


        public function visitors()
        { 
            
            if($_POST){
              //  dd(request()->all());
              $start_date = request('start_date');
              $to_date = request('to_date');
            }else{
                $start_date = date("Y-01-01");
                $to_date = date("Y-m-d");
            }    
            $week = date('Y-m-d', strtotime('-7 days'));
            $month = (int)date('m');
           // dd(date('m'));
            $this->data['this_year'] = \App\Models\Visitor::whereYear('created_at', date('Y'))->count();
            $this->data['this_month'] = \App\Models\Visitor::whereYear('created_at', date('Y'))->whereMonth('created_at', $month)->count();
            $this->data['this_week'] = \App\Models\Visitor::where('created_at', '>=', $week)->count();
            $this->data['users'] = \App\Models\Visitor::whereDate('created_at', '>=', $start_date)->whereDate('created_at', '<', $to_date)->get();
            $this->data['dates'] = DB::select('SELECT distinct DATE(created_at) as date, count(id) AS total FROM visitors group by DATE(created_at) order by DATE(created_at) desc');   
            $this->data['works'] = \App\Models\WorkStatus::get();
            $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM visitors t WHERE YEAR(t.created_at)=YEAR(CURDATE())  GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
            $this->data["title"] = "Church Visitors Trend";
            return view('admin.users.visitors', $this->data);
        }
    
        public function addVisitor(){
         
            if($_POST){
                //  dd(request()->all());
                  $data = request()->except(['phone', 'email']);
                  $phone = request('phone') !='' ? validate_phone_number(trim(request('phone')))[1] : '';
                  $email = request('email') == '' ? random_username(request('name')).'@albolink.com' : request('email');
                  $user =  \App\Models\Visitor::create(array_merge($data, ['password' => bcrypt(request('phone')), 'phone' => $phone, 'email' => $email, 'status' => 1]));
                  return redirect('users/visitors')->with('success', 'All New Members Added Successfully!');
                } 
                $this->data['users'] = \App\Models\User::get();
                $this->data['works'] = \App\Models\WorkStatus::get();
                return view('admin.users.add_visitor', $this->data);
            }
    

        public function Vist_report()
        {
            $type = request()->segment(3);
            $id = request()->segment(4);
            if($id != '' && $type != ''){
                if($type == 'week'){
                    $number = $id * -7;
                    $week = "'".date('Y-m-d', strtotime($number. ' days'))."'";
                  //  dd($week);
                    $this->data['users'] = \App\Models\Visitor::whereYear('created_at', date('Y'))->whereDate('created_at', '>', $week)->get();
                    $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM visitors t WHERE YEAR(t.created_at)=YEAR(CURDATE()) AND created_at>'.$week.'  GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
                    $this->data["title"] = "Church Visitors for Last ". number_to_words($id) .' Weeks.' ;
                }elseif($type == 'month'){
                    $this->data['users'] = \App\Models\Visitor::whereYear('created_at', date('Y'))->whereMonth('created_at', (int)$id)->get();
                    $this->data["datas"] = DB::SELECT("SELECT CONCAT(YEAR(created_at), '/', WEEK(created_at)) as month,count(*) as ynumber FROM visitors t WHERE YEAR(t.created_at)=YEAR(CURDATE())  AND MONTH(t.created_at)=".(int)$id." GROUP BY CONCAT(YEAR(created_at), '/', WEEK(created_at)) ORDER  BY CONCAT(YEAR(created_at), '/', WEEK(created_at)) ASC");
                    //DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM visitors t WHERE YEAR(t.created_at)=YEAR(CURDATE())  GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
                    $this->data["title"] = "Church Visitors Trend on " .\DateTime::createFromFormat('!m', $id)->format('F');;
                }elseif($type == 'location'){
                    $address = "'".$id."'";
                    $this->data['users'] = \App\Models\Visitor::where('address', $id)->get();
                    $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM visitors t WHERE YEAR(t.created_at)=YEAR(CURDATE()) and address = '.$address.' GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
                    $this->data["title"] = "Church Visitors from ". $id;
                }else{
                    $this->data['users'] = [];
                    $this->data["datas"] = [];
                    $this->data["title"] = "Church Visitors Trend";   
                }
            }else{
                $this->data['users'] = \App\Models\Visitor::get();
                $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, MONTH(t.created_at) as month FROM visitors t WHERE YEAR(t.created_at)=YEAR(CURDATE())  GROUP BY MONTH(t.created_at) ORDER BY MONTH(t.created_at) ASC');
                $this->data["title"] = "Church Visitors Trend";
            }
           
            $this->data['locations'] = DB::select('SELECT distinct address, count(address) AS total FROM visitors group by address order by count(address) desc');   
            return view('admin.users.visitor_report', $this->data);
        }


        public function editAll() {
    
            $this->data['users'] = \App\Models\User::where('status', 1)->get();
            $this->data['zones'] = \App\Models\Zone::get();
            return view('admin.users.edit_all', $this->data);
        }

        
    function single_add() {
        $id = request('user_id');
        $type = request('type');
        $reason_id = request('reason_id');
        if ((int)$id > 0 && $reason_id !='') {
           $update = \App\Models\User::where('id', $id)->update(["$type" => $reason_id]);
           if($update){
            $user = \App\Models\User::where('id', $id)->first();
            echo $user->name .' '. ucfirst($type) . '  Updated ';
           }else{
            echo $user->name .' '. ucfirst($type) . '  Failed Updated ';
           }
        }else{
            echo 'Failed Updated ';
        }
    }

    public function exports(){
        return Excel::download(new UsersExport, 'Exported_members.xlsx');
    }

    public function export_visitor(){
        return Excel::download(new VisitorExport, 'exported_visitors.xlsx');
    }
}
