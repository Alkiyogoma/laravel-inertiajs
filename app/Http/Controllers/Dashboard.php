<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Home_Controller
{
    public function __invoke(Request $request)
    {
    //     $this->data['setting'] = DB::table('setting')->orderBy('id', 'DESC')->first();
    //    // return redirect()->guest(route('admin/dashboard'));
    }
    public function index(){
            $id = request()->segment(3);
            if($id == 'month'){
                $this->data['show_month'] = 'month';
            }
            $this->data['users'] = \App\Models\User::where('status', 1)->count();
            $this->data['groups'] = \App\Models\Group::count();
            $this->data['leaders'] = \App\Models\Leader::orderBy('role_id', 'ASC')->get();
            $this->data['posts'] = \App\Models\Post::count();
            $this->data['family'] = \App\Models\Family::count();
            $this->data['zone'] = \App\Models\Zone::count();
            $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, date FROM attendance WHERE reason_id=1 group by date order by date desc limit 14');
            $this->data['families'] = \App\Models\Family::count();
            $this->data['user_info'] = DB::table('users')->select('sex', DB::raw('count(*) as total'))->groupBy('sex')->get();
            $this->data['sex_group'] = DB::table('users')->select('sex', DB::raw('count(*) as total'))->groupBy('sex')->get();
        
            $this->data['age_group'] = []; 
            $this->data['zone_group'] = DB::SELECT("with totals as (select count(0) AS total, zone from " . church_name() . ".users group by zone), zone_male as (select count(0) AS male, zone from " . church_name() . ".users where lower(sex) = 'male' group by zone), 
            zone_female as (select count(0) AS female, zone AS zone from " . church_name() . ".users where lower(sex) = 'female' group by zone)
            select a.male AS male,a.zone,b.total AS total,c.female AS female from ((zone_male a join totals b on(b.zone = a.zone)) left join zone_female c on(c.zone = a.zone)) ;
            ");
            return view('admin.dashboard.index', $this->data);
        }

        public function index2(){
            $this->data['users'] = \App\Models\User::where('status', 1)->count();
            $this->data['groups'] = \App\Models\Group::count();
            $this->data['leaders'] = \App\Models\Leader::orderBy('role_id', 'ASC')->limit(5)->get();
            $this->data['posts'] = \App\Models\Post::count();
            $this->data['family'] = \App\Models\Family::count();
            $this->data['zone'] = \App\Models\Zone::count();
            $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, date FROM attendance WHERE reason_id=1 group by date order by date desc limit 14');
            $this->data['families'] = \App\Models\Family::count();
            $this->data['user_info'] = DB::table('users')->select('sex', DB::raw('count(*) as total'))->groupBy('sex')->get();
            $this->data['age_group'] = [];  
            $this->data['sex_group'] =  DB::table('users')->select('sex', DB::raw('count(*) as total'))->groupBy('sex')->get();
            $this->data['zone_group'] = DB::SELECT("with totals as (select count(0) AS total, zone from " . church_name() . ".users group by zone), zone_male as (select count(0) AS male, zone from " . church_name() . ".users where lower(sex) = 'male' group by zone), 
            zone_female as (select count(0) AS female, zone AS zone from " . church_name() . ".users where lower(sex) = 'female' group by zone)
            select a.male AS male,a.zone,b.total AS total,c.female AS female from ((zone_male a join totals b on(b.zone = a.zone)) left join zone_female c on(c.zone = a.zone)) ;
            ");
            $this->data['events'] = \App\Models\Event::orderBy('id', 'DESC')->limit(5)->get();

            return view('admin.dashboard.index2', $this->data);
    }



    public function church(){
        $this->data['users'] = \App\Models\Church::get();   
        $this->data['locations'] = DB::select('SELECT distinct location, count(location) AS total FROM churchs group by location order by count(location) desc');   
        return view('admin.dashboard.church', $this->data);
    }


    public function profile(){
        $id = request()->segment(3);
        $this->data['church'] = \App\Models\Church::where('id', $id)->first();   
        return view('admin.dashboard.profile', $this->data);
    }

    
    public function saveComment()
    { 
       if($_POST){
          $post = \App\Models\Updates::create(request()->all());
          $comment ='';
          $comment .= '<li class="media">
            <img alt="image" class="mr-3 rounded-circle" width="70" src="'.url('public/assets/img/avatar/avatar-3.png').'">
            <div class="media-body">
                <div class="media-right"><div class="text-warning">Pending</div></div>
                <div class="media-title mb-1">'.$post->user->name.'</div>
                <div class="text-time">'.timeAgo($post->created_at).'</div>
                <div class="media-description text-muted">'.$post->content.'
                </div>
                <div class="media-links">
                <a href="#">View</a>
                <div class="bullet"></div>
                <a href="#">Edit</a>
                <div class="bullet"></div>
                <a href="#" class="text-danger">Trash</a>
                </div>
            </div>
            </li>';
         
        echo $comment;
        return redirect()->back()->with('success', 'Congraturation Your Comment Received..' );

       }
    }

    public function error(){
        $notification = array(
            'message' => 'Post created successfully!',
            'alert-type' => 'success'
        );
       return view('setting.error');
    }
    
    public function delete($id)
    {
        $delete = User::where('id', $id)->delete();
        // check data deleted or not
        if ($delete == 1) {
            $success = true;
            $message = "User deleted successfully";
        } else {
            $success = true;
            $message = "User not found";
        }
        //  Return response
        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function send_message(){
        
        if($_POST){
            //Send SMS to all Churches
            if(request('location') != '' && request('location')[0] == 'all'){
                $users = \App\Models\Church::where('status', 1)->get();
            }elseif(request('location') != ''){
                $users = \App\Models\Church::whereIn('location', request('location'))->get();
            }
      
            //Send SMS to all zone/section/cells
            if(request('location') != '' && request('location')[0] == 'all'){
                $users = \App\Models\Church::where('status', 1)->get();
            }elseif(request('location') != ''){
                $users = \App\Models\Church::whereIn('location', request('location'))->get();
            }
      
           $message = request('body');
            if (count($users) > 0 && strlen($message) > 10) {  
                foreach($users as $user){      
                    $body = str_replace('#name', $user->name, $message);
                    $sent = \App\Models\Message::create(['title' => $user->name.' New Message', 'user_id' => $user->id, 'phone' => validate_phone_number(trim($user->phone))[1], 'body' => $body, 'status' => 1]);
                   send_sms(str_replace('+', '', validate_phone_number(trim($user->phone))[1]), $body);
                }
                return redirect('dashboard/church')->with('success', 'Congraturations ' . count($users) . ' Messages Sent to churches Successfully');
            }else{
                return redirect()->back()->with('error', 'Your Message Should have atleast 10 Words. Please try Again!!!');
            }
        }
    }

    
        public function search() {
    
            $search = request('search');
            if(strlen($search) > 2){

                $users = \App\Models\User::where('name', 'like', "%{$search}%")->where('status', 1)
                ->orWhere('email', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->limit(10)->get();
               
             echo '<div class="search-header">Members Search Lists</div>';
                foreach($users as $user){
                       if(strtolower($user->sex) == 'male'){
                    
                   echo '<div class="search-item">
                        <a href="'.url("users/profile/$user->id").'">
                          <div class="search-icon bg-danger text-white mr-3">
                            <i class="fas fa-user"></i> </div>'. $user->name .' -  '.$user->phone.'
                        </a>
                      </div>';
                      }  else{ 
                      echo '<div class="search-item">
                        <a href="'.url("users/profile/$user->id").'">
                          <div class="search-icon bg-primary text-white mr-3">
                            <i class="fas fa-female"></i>
                          </div>
                          '. $user->name .' -  '. $user->phone .'
                        </a>
                      </div>';
                    }
                }
            }else{
                echo '<div class="search-header">Member with '.$search.' Not Found</div>'; 
            }
        }


        public function sendSMS(){
            $users = [];
            $list_date = [];
            //Send SMS to Visitors Member
            if(request('dates')[0] == 'all'){
                $users = \App\Models\Visitor::whereNotNull('phone')->get();
            }elseif(request('dates') != '' && request('dates') != 0){
                $users = \App\Models\Visitor::whereDate('created_at', request('dates'))->whereNotNull('phone')->get();
            }else{
                $users = [];
            }
            $message = request('body');
            if (count($users) > 0 && strlen($message) > 10) {  
                foreach($users as $user){ 
                   if($user->phone != ''){
                    $body = str_replace('#name', $user->name, $message);
                    $sent = \App\Models\Message::create(['title' => $user->name.' New Message', 'user_id' => $user->id, 'phone' => validate_phone_number(trim($user->phone))[1], 'body' => $body, 'status' => 0]);
                  //  send_sms(str_replace('+', '', validate_phone_number(trim($user->phone))[1]), $body);
                }
          //      send_sms(str_replace('+', '', validate_phone_number(trim(Auth::User()->phone))[1]), 'Sample:  '. $body);
                return redirect()->back()->with('success', 'Congraturations ' . count($users) . ' Messages Sent Successfully');
            }
            }else{
                return redirect()->back()->with('error', 'Your Message Should have atleast 10 Words. Please try Again!!!');
            }
        }
    
    }
    