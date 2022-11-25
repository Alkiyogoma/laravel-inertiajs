<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collect;
use App\Models\Envelope;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App;
use Auth;
use DB;
class Giving extends Home_Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    { 

        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['contributions']  = \App\Models\Contribution::get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['collects'] = App\Models\Collect::get();
        return view('giving.reports', $this->data);
    }

    public function offerings()
    { 
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['contributions']  = \App\Models\Contribution::whereIn('category_id', [1,4])->get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['collects'] = App\Models\Collect::whereIn('contribution_id', \App\Models\Contribution::whereIn('category_id', [1,4])->get(['id']))->get();
        return view('giving.offering', $this->data);
    }

    public function tithes()
    { 
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['contributions']  = \App\Models\Contribution::get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['collects'] = App\Models\Collect::where('contribution_id', 2)->get();
        return view('giving.tithes', $this->data);
    }

    public function projects()
    { 
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['contributions']  = \App\Models\Contribution::where('category_id', 5)->get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['collects'] = App\Models\Collect::whereIn('contribution_id', \App\Models\Contribution::where('category_id', 5)->get(['id']))->get();
        return view('giving.projects', $this->data);
    }

    public function messages()
    {
        if($_POST){
             //Send SMS to all members
             if(request('project_id') != '' && request('body')){
                $users = \App\Models\User::where('status', 1)->whereNotNull('phone')->get();
            $id = request('project_id');
            $message = request('body');
            $project = \App\Models\Contribution::where('id', $id)->first();
            if (count($users) > 0 && strlen($message) > 10) {
            foreach($users as $user){ 
                $paid_amount = App\Models\Collect::where('contribution_id', $project->id)->where('user_id', $user->id)->sum('amount');
               if((int) $paid_amount < (int)$project->upper_amount){
                    $balance_amount = (int)$project->upper_amount - (int)$paid_amount;

                    $body = str_replace('#name', $user->name, $message);
                    $body_two = str_replace('#amount', $balance_amount, $body);

                    $sent = App\Models\Message::create(['title' => $user->name.' New Message', 'user_id' => $user->id, 'phone' => validate_phone_number(trim($user->phone))[1], 'body' => $body_two, 'status' => 1]);
                   // send_sms(str_replace('+', '', validate_phone_number(trim($user->phone))[1]), $body_two);
                }
                return redirect()->back()->with('success', 'Congraturations ' . count($users) . ' Messages Sent Successfully');
            }
            }else{
                    return redirect()->back()->with('error', 'Your Message Should have atleast 10 Words. Please try Again!!!');
                }
            }else{
                return redirect()->back()->with('error', 'Your Message Should have atleast 10 Words. Please try Again!!!');
            }
        }else{
            return redirect()->back()->with('error', 'Your Message Should have atleast 10 Words. Please try Again!!!');
        }
    }


    public function setting()
    { 
        $this->data['category']  = \App\Models\ContributionCategory::get();
        return view('giving.setting', $this->data);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        if($_POST){
            $user_id = empty(request('user_id')) ? Auth::user()->id : request('user_id');
            $data = request()->except('user_id','_token');
            //dd($user_id);
            Collect::create(array_merge($data, ['reference' => date("Ymis"), 'user_id' => $user_id, 'added_by' => Auth::user()->id]));
            return redirect()->back()->with('secondary', 'Congraturation New Contribution Received..' );
        }
    }

    public function addGiving()
    {
        if($_POST){
            dd(request()->all());
            $data = request()->all();
            \App\Models\Contribution::create($data);
            return redirect()->back()->with('success', 'Congraturation New Giving Added..' );
        }
    }


    public function show()
    {
        $id = request()->segment(3);
        $this->data['collect'] = Collect::where('id', $id)->first();
        return view('giving.view', $this->data);
    }

    
    public function report()
    {
        $id = request()->segment(3);
        $this->data['Collect'] = Collect::where('id', $id)->first();

        $this->data['student_by_class'] = DB::table('age_group')->get();
        
        return view('giving.reports.'.$id, $this->data);
    }

    public function receipt()
    {
        $id = request()->segment(3);
        $this->data['receipt'] = Collect::where('id', $id)->first();
        return view('giving.reports.receipt', $this->data);
    }

    public function projectReceipt()
    {
        $id = request()->segment(3);
        $this->data['receipt'] = Collect::where('id', $id)->first();
        return view('giving.receipt', $this->data);
    }

    public function saveComment()
    { 
       if($_POST){
          $Collect = \App\Models\PostComment::create(request()->all());
          $comment ='';
          $comment .= '<li class="media">
            <img alt="image" class="mr-3 rounded-circle" width="70" src="'.url('public/assets/img/avatar/avatar-3.png').'">
            <div class="media-body">
                <div class="media-right"><div class="text-warning">Pending</div></div>
                <div class="media-title mb-1">'.$Collect->user->name.'</div>
                <div class="text-time">'.timeAgo($Collect->created_at).'</div>
                <div class="media-description text-muted">'.$Collect->content.'
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


        public function loadpage()
        {
            $id = request('id');
            $this->data['methods']  = \App\Models\PaymentMethod::get();
            $this->data['contributions']  = \App\Models\Contribution::get();
            $this->data['envelopes'] = App\Models\Envelope::get();
            $this->data['projects'] =\App\Models\Contribution::where('category_id', 5)->get();
            return view('giving.setting.'.$id, $this->data);
        }

        public function editGiving()
        { 
            $id = request()->segment(3);
            if($_POST){
                $data = request()->except('_token');
                \App\Models\Contribution::where('id', $id)->update($data);
                return redirect('giving/setting')->with('success', 'Giving Updated Successfully..' );
        }
            $this->data['contribution']  = \App\Models\Contribution::where('id', $id)->first();
            $this->data['category']  = \App\Models\ContributionCategory::get();
            return view('giving.setting.edit', $this->data);
        }


/*

This code is Owned and Managed by Albogast D. Kiyogoma
Phone: +255 744 158 016
Email: albogasty@gmail.com
Email: albogast@albolink.com
Address: P.O Box 75887 Dar es Salaam

*/
        
public function  getWeeks($month,$year){
    $month = intval($month);				//force month to single integer if '0x'
    $suff = array('st','nd','rd','th','th','th'); 		//week suffixes
    $end = date('t',mktime(0,0,0,$month,1,$year)); 		//last date day of month: 28 - 31
      $start = date('w',mktime(0,0,0,$month,1,$year)); 	//1st day of month: 0 - 6 (Sun - Sat)
    $last = 7 - $start; 					//get last day date (Sat) of first week
    $noweeks = ceil((($end - ($last + 1))/7) + 1);		//total no. weeks in month
    $output = "";						//initialize string		
    $output .= "<table class='table table-striped'><thead><tr><th>Weeks</th>";
    $monthlabel = str_pad($month, 2, '0', STR_PAD_LEFT);
    $conts = \App\Models\Contribution::get();
   
    for($x=1;$x<$noweeks+1;$x++){	
        if($x == 1){
            $starthate = "$year-$monthlabel-01";
            $day = $last - 6;
        }else{
            $day = $last + 1 + (($x-2)*7);
            $day = str_pad($day, 2, '0', STR_PAD_LEFT);
            $starthate = "$year-$monthlabel-$day";
        }
        if($x == $noweeks){
            $enddate = "$year-$monthlabel-$end";
        }else{
            $dayend = $day + 6;
            $dayend = str_pad($dayend, 2, '0', STR_PAD_LEFT);
            $enddate = "$year-$monthlabel-$dayend";
        }
        $output .= " <th class='text-center'>{$x}{$suff[$x-1]} week </th>";
    }
    $output .= "<th class='text-center'>Total</th></thead></tr>";	

    $output .= "<tbody>";
    $output .= "<tr><th>Date<br> Name</th>";
    $i = 1;
    for($x=1;$x<$noweeks+1;$x++){	
        if($x == 1){
            $starthate = "01-$monthlabel-$year";
            $day = $last - 6;
        }else{
            $day = $last + 1 + (($x-2)*7);
            $day = str_pad($day, 2, '0', STR_PAD_LEFT);
            $starthate = "$day-$monthlabel-$year";
        }
        if($x == $noweeks){
            $enddate = "$end-$monthlabel-$year";
        }else{
            $dayend = $day + 6;
            $dayend = str_pad($dayend, 2, '0', STR_PAD_LEFT);
            $enddate = "$dayend-$monthlabel-$year";
        }
      //  $output .= "<tr> <th>{$x}{$suff[$x-1]} week </th> </tr>";
        $output .= "<th class='text-center'>$starthate <br> $enddate </th>";

    }
    $output .= "<th>-</th></tr>";
        
    foreach($conts as $type){
        
        $output .= "<tr><th>$type->name</th>";
        $sum = 0;
        for($x=1;$x<$noweeks+1;$x++){
           
            if($x == 1){
                $starthate = "01-$monthlabel-$year";
                $day = $last - 6;
            }else{
                $day = $last + 1 + (($x-2)*7);
                $day = str_pad($day, 2, '0', STR_PAD_LEFT);
                $starthate = "$day-$monthlabel-$year";
            }
            if($x == $noweeks){
                $enddate = "$end-$monthlabel-$year";
            }else{
                $dayend = $day + 6;
                $dayend = str_pad($dayend, 2, '0', STR_PAD_LEFT);
                $enddate = "$dayend-$monthlabel-$year";
            }
            $mwanzo = date("Y-m-d", strtotime($starthate));
            $mwisho = date("Y-m-d", strtotime($enddate));
          
          $amount = $type->collections->whereBetween('date', [$mwanzo, $mwisho])->sum('amount');
          $sum += $amount;	
          $output .= "<th class='text-center'>". $amount ."</th>";
        }
        $output .= "<th class='text-center'>$sum</th></tr>";

    }

        $output .= "<tfooter><tr><th>Total</th>";
        $sum1 = 0;

        for($x=1;$x<$noweeks+1;$x++){
           
            if($x == 1){
                $starthate = "01-$monthlabel-$year";
                $day = $last - 6;
            }else{
                $day = $last + 1 + (($x-2)*7);
                $day = str_pad($day, 2, '0', STR_PAD_LEFT);
                $starthate = "$day-$monthlabel-$year";
            }
            if($x == $noweeks){
                $enddate = "$end-$monthlabel-$year";
            }else{
                $dayend = $day + 6;
                $dayend = str_pad($dayend, 2, '0', STR_PAD_LEFT);
                $enddate = "$dayend-$monthlabel-$year";
            }
            $mwanzo1 = date("Y-m-d", strtotime($starthate));
            $mwisho1 = date("Y-m-d", strtotime($enddate));
          
          $amount1 = \App\Models\Collect::whereBetween('date', [$mwanzo1, $mwisho1])->sum('amount');
          $sum1 += $amount1;	
          $output .= "<th class='text-center'>". $amount1 ."</th>";
        }
        $output .= "<th>$sum1</th></tr>";

    $output .= "</tbody></tfooter></table>";
    return $output;
    }


/*

This code is Owned and Managed by Albogast D. Kiyogoma
Phone: +255 744 158 016
Email: albogasty@gmail.com
Email: albogast@albolink.com
Address: P.O Box 75887 Dar es Salaam

*/
    
    public function risiti()
    {
        $id = request('id');
        $this->data['methods']  = \App\Models\PaymentMethod::get();
        return view('giving.reports.receipt', $this->data);
    }

}
