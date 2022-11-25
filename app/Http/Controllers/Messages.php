<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\{UserUpdateRequest,UserAddRequest};
use Spatie\Permission\Models\Role;
use Auth;
use DB;

class Messages extends Home_Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
            $this->data['users'] = User::get();
        return view('message.report', $this->data);
    }

    public function inbox()
    { 
        $this->data['messages'] = \App\Models\Message::where('user_id', Auth::User()->id)->orderBy('id', 'DESC')->get();
        return view('message.inbox', $this->data);
    }

    public function sent()
    { 
        $this->data['messages'] = \App\Models\Message::whereIn('user_id', \App\Models\User::where('status', 1)->get(['id']))->paginate(20);
        return view('message.sent', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserAddRequest $request)
    {
        $user = User::create($request->all());
        $role = Role::find($request->role);
        if($role)
        {
            $user->assignRole($role);
        }
        return response()->json($user);
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
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function sendSingle()
    {
        $id = request('user_id');
        $message = request('name') != '' ?  request('name') :  request('body');
        $user = User::where('id', $id)->first();
        if(request('type') != '' && request('type') == 'church'){
            $user = \App\Models\Church::where('id', $id)->first();
        }
        if ($user) {
            if(strlen($message) > 10){
            send_sms(str_replace('+', '', validate_phone_number(trim($user->phone))[1]), $message);
            $sent = Message::create(['title' => $user->name.' Sent Message', 'user_id' => $id, 'phone' => validate_phone_number(trim($user->phone))[1], 'body' => $message, 'status' => 1]);
            if($sent){
                return request('body') != '' ? redirect()->back()->with('success', 'SMS Sent to '.$user->name) : '';

                return response()->json([
                "title" => "SMS Sent to ".$user->name,
                "text" => $message,
                "icon" => "success"
            ]);
        }
        }else{
            return response()->json([
                "title" => Auth::User()->name,
                "text" => "You have to write something to send this message",
                "icon" => "error"
            ]);
        }
        } else {
            return response()->json([
                "title" => "Message Failed!",
                "text" => 'User Information Not Found',
                "icon" => "error"
            ]);
        }
    }

        public function send()
        {
            $this->data['users'] = \App\Models\User::get();
            $this->data['groups'] = \App\Models\Group::get();
            $this->data['families'] = \App\Models\Family::get();
            $this->data['leaders'] = \App\Models\Leader::get();

            if($_POST){
                //Send SMS to Leaders
                $users = [];
                if(request('leader_id') != '' && request('leader_id')[0] == 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\Leader::where('status', 1)->get(['user_id']))->get();
                }elseif(request('leader_id') != ''){
                    $users = \App\Models\User::whereIn('id', request('leader_id'))->get();
                }

                //Send SMS to Family Member
                if(request('family_id') != '' && request('family_id')[0] == 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\Family::where('status', 1)->get(['user_id']))->get();
                }elseif(request('family_id') != ''){
                    $users = \App\Models\User::whereIn('id', \App\Models\Family::where('status', 1)->where('id', request('family_id'))->get(['user_id']))->get();
                }
               
                //Send SMS to Visitors Member
                if(request('visitor_id') != '' && request('visitor_id')[0] == 'all'){
                    $users = \App\Models\Visitor::where('status', 1)->get();
                }elseif(request('visitor_id') != ''){
                    $users = \App\Models\Visitor::whereIn('id', request('visitor_id'))->get();
                }

                //Send SMS to Believer Member
                if(request('believer_id') != '' && request('believer_id')[0] == 'all'){
                    $users = \App\Models\Believer::where('status', 1)->get();
                }elseif(request('believer_id') != ''){
                    $users = \App\Models\Believer::whereIn('id', request('believer_id'))->get();
                }

                //Send SMS to Visitors Member
                $zone = request('zone_id');
                $section = request('section_id');
                $cell = request('cell_id');

                //send Sms to Cell Members
                if(request('type_name') == 'Cell' && $cell != '' && request('zone_member_id')[0] == 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\CellMember::where('cell_id',  $cell)->get(['member_id']))->get();
                }elseif(request('type_name') == 'Cell' && $cell != '' && request('zone_member_id')[0] != 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\CellMember::whereIn('id', request('zone_member_id'))->get(['member_id']))->get();
                }

                //send Sms to Section Members
                if(request('type_name') == 'Section' && $section != '' && request('zone_member_id')[0] == 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\CellMember::whereIn('cell_id',  \App\Models\Cells::where('section_id', $section)->get(['id']))->get(['member_id']))->get();
                }elseif(request('type_name') == 'Section' && $section != '' && request('zone_member_id')[0] != 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\CellMember::whereIn('id', request('zone_member_id'))->get(['member_id']))->get();
                }

                //send Sms to Zone Members
                if(request('type_name') == 'Zone' && $zone != '' && request('zone_member_id')[0] == 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\CellMember::whereIn('cell_id',  \App\Models\Cells::whereIn('section_id', \App\Models\Section::where('zone_id', $zone)->get(['id']))->get(['id']))->get(['member_id']))->get();
                }elseif(request('type_name') == 'Zone' && $zone != '' && request('zone_member_id')[0] != 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\CellMember::whereIn('id', request('zone_member_id'))->get(['member_id']))->get();
                }

                //Send SMS to Specific Group
                if(request('groups_id') != '' && request('groupmember_id') != ''){
                    if(request('groupmember_id') != '' && request('groupmember_id')[0] == 'all'){
                        $users = \App\Models\User::whereIn('id', \App\Models\GroupMember::where(['status' => 1, 'group_id'=> request('groups_id')])->get(['member_id']))->get();
                    }elseif(request('groupmember_id') != '' && request('groupmember_id')[0] != 'all'){
                        $users = \App\Models\User::whereIn('id', \App\Models\GroupMember::where('status', 1)->where('member_id', request('groupmember_id'))->get(['member_id']))->get();
                    }
                }

                //Send SMS to Groups
                if(request('group_id') != '' && request('group_id')[0] == 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\GroupMember::where('status', 1)->get(['member_id']))->get();
                }elseif(request('group_id') != '' && request('group_id')[0] != 'all'){
                    $users = \App\Models\User::whereIn('id', \App\Models\GroupMember::where('status', 1)->whereIn('group_id', request('group_id'))->get(['member_id']))->get();
                }

                //Send SMS to all members
                if(request('member_id') != '' && request('member_id')[0] == 'all'){
                    $users = \App\Models\User::where('status', 1)->get();
                }elseif(request('member_id') != ''){
                    $users = \App\Models\User::whereIn('id', request('member_id'))->get();
                }


                $message = request('body');
                if (count($users) > 0 && strlen($message) > 15) {  
                    foreach($users as $user){ 
                       if($user->phone != ''){
                        $body = str_replace('#name', $user->name, $message);
                        Message::create(['title' => $user->name.' New Message', 'user_id' => $user->id, 'phone' => validate_phone_number(trim($user->phone))[1], 'body' => $body, 'status' => 0]);
                      //  send_sms(str_replace('+', '', validate_phone_number(trim($user->phone))[1]), $body);
                    }
                }
                return redirect()->back()->with('success', 'Congraturations ' . count($users) . ' Messages Sent Successfully');
            }else{
                    return redirect()->back()->with('error', 'Your Message Should have atleast 10 Words. Please try Again!!!');
                }
            }
            $setting = DB::table('setting')->first()->sms_enabled;
            if((int)$setting < 20){
                return redirect('messages/setting')->with('error', 'Your Message balance is less than 20 SMS. Please buy new sms now!!');
            }
            return view('message.add', $this->data);
        }

        public function setting()
        {
            $week = date('Y-m-d', strtotime('-7 days'));
            $month = (int)date('m');
            $this->data['this_day'] = \App\Models\Message::whereYear('created_at', date('Y'))->whereDate('created_at', date("Y-m-d"))->count();
            $this->data['this_month'] = \App\Models\Message::whereYear('created_at', date('Y'))->whereMonth('created_at', $month)->count();
            $this->data['this_week'] = \App\Models\Message::where('created_at', '>=', $week)->count();
            $this->data["datas"] = DB::SELECT('SELECT count(id) as ynumber, t.created_at::date as date FROM messages t  GROUP BY t.created_at::date ORDER BY t.created_at::date desc limit 8');
            return view('message.reports', $this->data);
        }
        
        public function trynew()
        {
            return view('layouts.default');
        }

        public function buySMS()
        {
            $message =  church_name(). ' ';  
            $message .= request('comment');
            $message .= chr(10);
            $message .= 'Number of SMS - '. request('message');   
            $message .= chr(10);
            $message .= 'Phone - '. request('phone');
            $message .= chr(10);
            $message .= 'Method - '. request('method');
           send_sms('255744158016', $message);
            if(Auth::User()->phone != ''){
            send_sms(Auth::User()->phone, 'Hello '.Auth::User()->name .', KANISALINK have Received your order to buy '. request('message'). ' SMS we will send you invoice soon.');
           }
           return redirect()->back()->with('success', 'We have Received Your Order of ' . request('message') . ' SMS we will send you invoice to pay.');
        }
}