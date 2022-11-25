<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collect;
use App\Models\Envelope;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\{UserUpdateRequest,UserAddRequest};
use Spatie\Permission\Models\Role;
use App;
use Auth;
use DB;

class Attendances extends Home_Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        //$this->authorize(User::class, 'index');
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['contributions']  = \App\Models\Contribution::get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['collects'] = App\Models\Collect::get();
        return view('attendance.index', $this->data);
    }

   
    public function church()
    { 
        if($_POST){
            $data = request()->except('_token');
         //   dd($data);
            \App\Models\ChurchAttendance::create($data);
            return redirect('attendances/church')->with('success', 'New Attendance Added Successfully..' );
    }
        $this->data["title"] = "Church Visitors Trend";
        $this->data['attendances']  = \App\Models\ChurchAttendance::get();
        return view('attendance.church', $this->data);
    }

    public function group()
    { 
        $this->data['set']  = $set = request()->segment(3);
        $this->data['groups']  = \App\Models\Group::get();
        $this->data['attendances']  = \App\Models\GroupAttendance::groupBy('date','group_id')->orderBy('date','DESC')->get(['date','group_id']);
        return view('attendance.group', $this->data);
    }

    public function zone()
    { 
        $this->data['set']  = $set = request()->segment(3);
        $this->data['groups']  = \App\Models\Zone::get();
        $this->data['attendances']  = \App\Models\CellAttendance::groupBy('date','cell_id')->orderBy('date','DESC')->get(['date','cell_id']);
        return view('attendance.zone', $this->data);
    }

    public function event()
    { 
        $this->data['set']  = $set = request()->segment(3);
        $this->data['events']  = \App\Models\Event::get();
        $this->data['attendances']  = \App\Models\GroupAttendance::groupBy('date','cell_id')->orderBy('date','DESC')->get(['date','cell_id']);
        return view('attendance.event', $this->data);
    }
    
    public function create()
    {
        $this->data['date']  = request()->segment(5);
        $this->data['id']  = (int)$id = request()->segment(4);
        $this->data['set']  = $set = request()->segment(3);
        if($set == 'zone' && $id > 0){
            $this->data['users']  = \App\Models\User::whereIn('id', \App\Models\SectionMember::whereIn('section_id',  \App\Models\Section::where('zone_id', $id)->get(['id']))->get(['member_id']))->get();
        }elseif($set == 'group' && $id > 0){
            $this->data['users']  = \App\Models\User::whereIn('id', \App\Models\GroupMember::where('group_id', $id)->get(['member_id']))->get();
        }elseif($set == 'event' && $id > 0){
            $this->data['users']  = \App\Models\User::whereIn('id', \App\Models\SectionMember::whereIn('section_id',  \App\Models\Section::where('zone_id', $zone)->get(['member_id'])))->get();
        }elseif($set == 'cell' && $id > 0){
            $this->data['users']  = \App\Models\User::whereIn('id', \App\Models\SectionMember::where('section_id', $id)->get(['member_id']))->get();
        }else{
            $this->data['users']  = [];
        }
            $this->data['reasons']  = \App\Models\Reason::orderBy('id', 'ASC')->get();
        return view('attendance.create', $this->data);
    }

    function single_add() {
        $id = request('user_id');
        $day = request('date');
        $category = request('category');
        request('category_id') != '' ? $category_id = request('category_id') : $category_id = 1;
        $reason_id = (int) request('reason_id');
        if ((int) $id && (int) $day) {
            $this->addSingle($id, $day,  $reason_id, $category, $category_id);
           $user = \App\Models\User::find($id);
            $reason = \App\Models\Reason::find($reason_id);
            echo $user->name .' is '. $reason->name;
        }
    }

    public function addSingle($user_id, $day,  $reason_id=null, $category, $category_id) {
        $where = ['user_id' => $user_id, 'date' => $day];
        $reason = \App\Models\Reason::find($reason_id);
        $user = \App\Models\User::find($user_id);
        $comment = "$user->name  is  $reason->name";
       // 'category' => , 
        //'category_id' => $category_id, 
        if($category == 'group'){
        $found = \App\Models\GroupAttendance::where($where);
        if (!empty($found->first())) {
            //update              
            $data = array_merge($where, ['added_by' => Auth::User()->id, 'group_id' => $category_id,  'comment' => $comment, 'reason_id'=>$reason_id]);
            $found->update($data);
        } else {
            \App\Models\GroupAttendance::create(array_merge($where, ['added_by' => Auth::User()->id, 'group_id' => $category_id, 'comment' => $comment, 'reason_id'=>$reason_id]));
        }
    }elseif($category == 'zone'){
        $found = \App\Models\CellAttendance::where($where);
        if (!empty($found->first())) {
            //update              
            $data = array_merge($where, ['added_by' => Auth::User()->id, 'cell_id' => $category_id,  'comment' => $comment, 'reason_id'=>$reason_id]);
            $found->update($data);
        } else {
            \App\Models\CellAttendance::create(array_merge($where, ['added_by' => Auth::User()->id, 'cell_id' => $category_id, 'comment' => $comment, 'reason_id'=> $reason_id]));
        }
    }

        return TRUE;
    }

    function all_add() {
        $classes = request('classes');
        $day = request('day');
        $category = request('category');
        $category_id = request('category_id');

        $present = request('status') == 'checked' ? 1 : 0;
        $students = \App\Model\Zone::where(['classesID' => $classes, 'status' => 1])->get();
        foreach ($students as $student) {
            $this->addSingle($student->user_id, $day, $present, $category, $category_id);
        }
        echo $this->lang->line('menu_success');
    }

    public function show() {
        $usertype = $this->session->userdata("usertype");
        if ($usertype == "Teacher" || can_access('view_student_attendance')) {
            $id = clean_htmlentities(($this->uri->segment(3)));
            $url = clean_htmlentities(($this->uri->segment(4)));
            $date = clean_htmlentities(($this->uri->segment(5)));

            if ((int) $id && (int) $url) {
                $this->data["student"] = Student::find($id);
                $this->data["classes"] = Classes::find($url);
                if ($this->data["student"] && $this->data["classes"]) {
                    $this->data['set'] = $url;

                    $this->data["subview"] = "Attendance/view";
                    $this->load->view('_layout_main', $this->data);
                } else {
                    $this->data["subview"] = "error";
                    $this->load->view('_layout_main', $this->data);
                }
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } elseif ($usertype == "Student") {
            $student = \App\Model\Student::find(Auth::User()->id);
            if ($student) {
                $this->data["student"] = $student;
                $this->data['classes'] = Classes::find($student->classesID);

                $this->data["section"] = $student->section;
                $this->data['set'] = $student->classesID;

                $this->data["subview"] = "Attendance/view";
                $this->load->view('_layout_main', $this->data);
            } else {
                $this->data["subview"] = "error";
                $this->load->view('_layout_main', $this->data);
            }
        } elseif ($usertype == "Parent") {
            $parent = \App\Model\Parents::find(Auth::User()->id);
            $this->data['allstudents'] = \App\Model\StudentParent::where('parent_id', $parent->parentID)->get();

            $id = clean_htmlentities(($this->uri->segment(3)));
            if ((int) $id) {
                $checkstudent = \App\Model\Student::find($id);
                if (!empty($checkstudent)) {
                    $classesID = $checkstudent->classesID;
                    $this->data['set'] = $id;
                    $this->data["student"] = $checkstudent;
                    $this->data['classes'] = Classes::find($classesID);
                    $this->data['attendances'] = $this->Attendance_m->get_order_by_attendance(array("user_id" => $id, "classesID" => $classesID));
                    $this->data["section"] = $checkstudent->section;

                    $this->data["subview"] = "Attendance/index_parent";
                    $this->load->view('_layout_main', $this->data);
                } else {
                    $this->data["subview"] = "error";
                    $this->load->view('_layout_main', $this->data);
                }
            } else {
                $this->data["subview"] = "Attendance/search_parent";
                $this->load->view('_layout_main', $this->data);
            }
        } else {
            $this->data["subview"] = "error";
            $this->load->view('_layout_main', $this->data);
        }
    }


    public function viewgroup()
    {
        $date =request()->segment(3);
        $type =request()->segment(4);
        $this->data["date"] = $date;
        $this->data["users"] = \App\Models\GroupAttendance::where('date', $date)->where('group_id', $type)->get();
        return view('attendance.view', $this->data);
    }

    public function viewzone()
    {
        $date =request()->segment(3);
        $type =request()->segment(4);
        $this->data["date"] = $date;
        $this->data["users"] = \App\Models\CellAttendance::where('date', $date)->where('cell_id', $type)->get();
        return view('attendance.view', $this->data);
    }

        public function profile()
        {
            $id =request()->segment(3);
            $this->data["user"] = \App\Models\User::where('id', $id)->first();
            $this->data["groups"] = \App\Models\Attendance::where('user_id', $id)->where('category', 'group')->get();
            $this->data["events"] =  \App\Models\Attendance::where('user_id', $id)->where('category', 'event')->get();
            $this->data["zones"] =  \App\Models\CellAttendance::where('user_id', $id)->get();
            $this->data["churchs"] =  \App\Models\Attendance::where('user_id', $id)->where('category', 'church')->get();
            return view('attendance.profile', $this->data);
        }

        public function load_user()
        {
            $users = \App\Models\User::whereIn('id', \App\Models\GroupMember::where('group_id', request('group_id'))->get(['member_id']))->get();
            $i =1;
            $reasons = \App\Models\Reason::get();
            $comment ='';
            $date = request('date');
            foreach($users as $user){
              
          $comment .= '<tr>
                <td>'.$i++.'</td>
                <td>'.$user->name.'</td>
                <td>'.$user->sex.'</td>
                <td>'.$user->phone.'</td>
                <td>'.$user->address.'</td>
                <td>'.substr($user->role->name, 0, 30).'</td>
                <td>'.$date.'</td>
                <td>
                  <select class="form-control" onclick="save_attendance('.$user->id.')" id="reason_'.$user->id.'" user="'.$user->id.'">
                         <option value="1">Select Here..</option>';
                          foreach($reasons as $reason){
                            $comment .=  '<option value="'.$reason->id.'">'.$reason->name.'</option>';
                        }
                   
                $comment .= '</select>
              </td>
            </tr>';
            }
            echo $comment; 
        }

        public function report()
        {
            $type =request()->segment(3);
            $id =request()->segment(4);
            if($type == 'week' && $id != ''){
                $number = $id * -7;
                $this->data['type'] = $type;
                $this->data['week'] = date('Y-m-d', strtotime($number. ' days'));
                $this->data["title"] = "Church Attendance for Last ". number_to_words($id) .' Weeks.' ;
            }elseif($type == 'date' && $id != ''){
                $number = $id;
                $this->data['type'] = $type;
                $this->data['week'] = "$number";
                $this->data["title"] = "Church Attendance on ". $id .' ('.date("l", strtotime($id)).').' ;
            }elseif($type == 'month' && $id != ''){
                $this->data["week"] = [];
                $this->data["month"] = (int) $id;
                $this->data["title"] = "Church Members Attendace Trend on "  .\DateTime::createFromFormat('!m', $id)->format('F');
            }else{
                $this->data["week"] = [];
                $this->data["month"] = date('m');
                $this->data["title"] = "Church Members Attendace Trend on " .date('F');
            }
        return view('attendance.report', $this->data);
    }

        public function updateInvoiceSetting($type) {
            if (request('id') != NULL) {
                $this->updateInvoiceSetting(request('id'));
            }
            $settings = \App\Model\Setting::where('id', $type)->first();
            $vars = get_object_vars(\DB::table('invoice_settings')->where('id', $type)->first());
            $obj = array();
            foreach ($vars as $key => $variable) {
                if (!in_array($key, array('created_at', 'id'))) {
                    $obj = array_merge($obj, array($key => request($key) == null ? 0 : request($key)));
                }
            }
            $settings->update($obj);
        }
    
}
