<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use DB;
use Excel;
use App\Notifications\FcmNotification;
use App;
use DateTime;
class Home_Controller extends Controller {

    public $user_access;
    public $settings;

    function __construct() {
        $this->middleware('auth');
        $this->data['all'] = array();
        $this->data['alert'] = array();
    //    $this->lang->load('topbar_menu');
    //    $this->lang->load('email');
        $this->resendSMS();

        $this->data['setting'] = DB::table('setting')->orderBy('id', 'DESC')->first();
        $this->data['birthdays'] = $this->getBirthdayUsers();
        $this->data['calendars'] = DB::table('events')->where('end_date', '>', now())->orderBy('id', 'ASC')->limit(3)->get();
        $this->data['messages'] = $this->newMessage();
        $this->data['all_members']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['positions'] = \App\Models\LeaderRole::get();
        $this->setting = $this->data["setting"]; 
        $this->data['setups'] = $this->setupInstructions();
        $this->data['setting'] = DB::table('setting')->first();

    }

    
    function newMessage() {
        return \App\Models\Message::where('user_id',  )->orderBy('id', 'DESC')->limit(3)->get();
    }

    function getBirthdayUsers() {
        $sql = "SELECT  a.name,  a.dob, a.zone FROM users a WHERE a.dob::date = NOW()";
        return DB::select($sql);
    }

    public function setupInstructions() {
//check priorities in messages

        if (DB::table('setting')->first()->sms_enabled < 51) {
            $status = array('title' => 'Please consider to Buy SMS', 'message' => 'You need to buy new sms your current balance is less than 50 SMS. NOTE System will fail to send sms.', 'link' => url("messages/setting"));
        } elseif (DB::table('setting')->count() == 0) {
            $status = array('title' => 'Church Details(Profile) Not Defined', 'message' => 'You need to add Church Information. The System will function effectively after adding about church profile.', 'link' => url("setting/index"));
        } else if (\App\Models\Zone::count() == 0) {
            $status = array('title' => 'Church Zones Not Defined', 'message' => 'You need to add Zones in your Church. The System will function effectively and help to analyze Zone Members.', 'link' => url("groups/zones"));
        } else if (\App\Models\User::count() < 3) {
            $status = array('title' => 'Church Members Not Added', 'message' => 'You need to add Church Members. The System will function effectively and help to identify key member Details.', 'link' => url("users/create"));
        } else if (\App\Models\Collect::count() == 0) {
            $status = array('title' => 'No Church Offerings Recorded', 'message' => 'You need to add giving & Offerings collection and system will help you to keep record of all giving collections and generate required reports.', 'link' => url("giving/offerings"));
        } else if (\App\Models\Family::count() == 0) {
            $status = array('title' => 'Church Member Families Not Defined', 'message' => 'You need to add Church Member Families. The System will function effectively and help to identify each member family destails.', 'link' => url("families/index"));
        } else if (\App\Models\Attendance::count() == 0) {
            $status = array('title' => 'Please Add Attendance Records', 'message' => 'You need to define Upcoming Events in your Church. The System will help to keep calendar and notify(SMS) you of all upcoming events.', 'link' => url("events/calendar"));
        }else if (\App\Models\Event::count() == 0) {
            $status = array('title' => 'Please Define Calendar and Events', 'message' => 'You need to attendance record of events in your Church. The System will help to keep record ang give you reports and analysis of members attendance.', 'link' => url("attendances/church"));
        } else if (\App\Models\Expense::count() == 0 || \App\Models\Revenue::count() == 0) {
            $status = array('title' => 'No Church Expenses Recorded', 'message' => 'You need to add Church Expenses & Revenues and system will help you to keep record of all expenses and give you church financial reports .', 'link' => url("accounts/expenses"));
        }else if (\App\Models\Leader::count() == 0) {
            $status = array('title' => 'Church Leaders Not Added', 'message' => 'You need to add Leaders in your Church. The System will function effectively and help to analyze Zone Members.', 'link' => url("admin/leaders"));
        } else if (\App\Models\Section::count() == 0 || \App\Models\Cells::count() == 0) {
            $status = array('title' => 'Church Section Not Defined', 'message' => 'You need to add Zones Cells & Sections in your Church. The System will function effectively and help to analyze Zone Members.', 'link' => url("groups/zones"));
        }  else {
            $status = array();
        }

        return $status;
    }

    public function resendSMS() {
        $messages = App\Models\Message::where('status', 0)->limit(40)->get();
        if (count($messages) > 0) { 
            foreach($messages as $message){ 
               // dd($message);
                App\Models\Message::where('id', $message->id)->update(['status' => 1, 'return_code' => 'Message Sent Successfuly']);
                send_sms(str_replace('+', '', validate_phone_number(trim($message->phone))[1]), $message->body);
                $setting = DB::table('setting')->first()->sms_enabled - 1;
                DB::table('setting')->update(['sms_enabled' => $setting]);
            }
            return true;
        }else{
            return true;
        }
    }

    public function get_setting() {
        return DB::table('setting')->orderBy('id', 'DESC')->first();
    }

}
