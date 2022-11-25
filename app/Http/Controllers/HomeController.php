<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
     //   $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    
    public function reset()
    {
        if($_POST){
            $data = request()->except(['_token','password','password_confirmation']);
             \App\Models\User::where($data)->update(['password' =>  bcrypt(request('password'))]);
            $user =  \App\Models\User::where($data)->first();
            $message = "Hello ". $user->name ." We've received a request to reset password for your KanisaLink Account. If you didn't make this request please reset your password now."; 
         send_sms(str_replace('+', '', validate_phone_number(trim($user->phone))[1]), $message);
         return redirect('/logout')->with('success', 'Congraturation Your Password Updated Successful' );

        }
    }


    public function comment()
    {
        if($_POST){
            $data = request()->except(['_token']);
             \App\Models\Comment::create($data);
        }
        return redirect()->back()->with('success', 'Your Comment Sent successfully');
    }

    public function users(){
        
        $users =  \App\Models\User::where('status', 1)->get();
        return response()->json($users);

    }

    public function groups(){
        $group = \App\Models\Group::get();
        return response()->json($group);
    }

    public function visitors(){
        $visitors = \App\Models\Visitor::whereYear('created_at', date('Y'))->get();
        return response()->json($visitors);
    }

}
