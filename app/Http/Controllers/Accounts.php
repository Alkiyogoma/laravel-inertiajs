<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\AccountGroup;
use App\Models\PaymentMethod;
use App\Models\User;
use Spatie\Permission\Models\Role;
use DB;
use Auth;
class Accounts extends Home_Controller
{

    public function index()
    { 
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['groups']  = \App\Models\AccountGroup::get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['revenues'] = \App\Models\Revenue::get();
        return view('account.revenue', $this->data);
    }

    public function revenues()
    { 
        $week = date('Y-m-d', strtotime('-7 days'));
        $month = (int)date('m');
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['groups']  = \App\Models\AccountGroup::get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['revenues'] = \App\Models\Revenue::get();
        $this->data['this_year'] = \App\Models\Revenue::whereYear('created_at', date('Y'))->sum('amount');
        $this->data['this_month'] = \App\Models\Revenue::whereYear('created_at', date('Y'))->whereMonth('created_at', $month)->sum('amount');
        $this->data['this_week'] = \App\Models\Revenue::where('created_at', '>=', $week)->sum('amount');
        return view('account.revenue', $this->data);
    }

    public function expenses()
    {
        $week = date('Y-m-d', strtotime('-7 days'));
        $month = (int)date('m');
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['groups']  = \App\Models\AccountGroup::get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['expenses'] = \App\Models\Expense::get();
        $this->data['this_year'] = \App\Models\Expense::whereYear('created_at', date('Y'))->sum('amount');
        $this->data['this_month'] = \App\Models\Expense::whereYear('created_at', date('Y'))->whereMonth('created_at', $month)->sum('amount');
        $this->data['this_week'] = \App\Models\Expense::where('created_at', '>=', $week)->sum('amount');
        return view('account.expense', $this->data);
    }

    public function projects()
    { 
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['contributions']  = \App\Models\Contribution::get();
        $this->data['users']  = \App\Models\User::orderBy('id', 'ASC')->get();
        $this->data['collects'] = \App\Models\Collect::whereNotIn('contribution_id', \App\Models\Contribution::whereIn('category_id', [2,1,4])->get(['id']))->get();
        return view('account.projects', $this->data);
    }

    public function charts()
    { 
        $this->data['accounts']  = AccountGroup::get();
        return view('account.charts', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        if($_POST){
                $data = request()->all();
                AccountGroup::create($data);
                return redirect()->back()->with('secondary', 'Congraturation New Account Group Added Successfully..' );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addExpense()
    {
        if($_POST){
            $data = request()->all();
            Expense::create(array_merge($data, ['reference' => date("Yhmhis"), 'added_by' => Auth::user()->id]));
            return redirect()->back()->with('secondary', ' New Account Expense Added Successfuly...' );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function addRevenue()
    {
        if($_POST){
            $data = request()->all();
            Revenue::create(array_merge($data, ['reference' => date("Yhmhis"), 'added_by' => Auth::user()->id]));
            return redirect()->back()->with('secondary', ' New Account Group Added Successfuly...' );
        }
    }

    public function Edit()
    {
        $id = request()->segment(3);
        
        if($_POST){
            $data = request()->except('_token');
            Revenue::where('id', $id)->update(array_merge($data, ['added_by' => Auth::user()->id]));
            return redirect('accounts/receipt/'.$id)->with('secondary', ' Revenue Updated Successfuly...' );
        }
        
        $this->data['payments']  = \App\Models\PaymentMethod::get();
        $this->data['groups']  = \App\Models\AccountGroup::get();
        $this->data['revenue']  = Revenue::where('id', $id)->first();
        return view('account.edit_revenue', $this->data);
    }


    public function addGiving()
    {
        if($_POST){
           // dd(request()->all());
                $data = request()->all();
                \App\Models\Contribution::create($data);
                return redirect()->back()->with('success', 'Congraturation New Giving Added..' );
        }
    }


    public function show()
    {
        $id = request()->segment(4);
        $type = request()->segment(3);
        if($type == 'revenue'){
            $this->data['account'] = Revenue::where('id', $id)->first();
        }else if($type == 'expense'){
            $this->data['account'] = Expense::where('id', $id)->first();
        }else{
            return redirect()->back()->with('message', 'Please Define Account Type & Try Again.' ); 
        }
        return view('account.view', $this->data);
    }

    
    public function report()
    {
        $group = request()->segment(3);
        $type = request()->segment(4);
        $id = request()->segment(5);

        if($type == 'week' && $id != ''){
            $number = $id * -7;
            $this->data['type'] = $type;
            $this->data['week'] = date('Y-m-d', strtotime($number. ' days'));
            $this->data["title"] = "Church Revenue Collection for Last ". number_to_words($id) .' Weeks.' ;
        }elseif($type == 'date' && $id != ''){
            $number = $id;
            $this->data['type'] = $type;
            $this->data['week'] = "$number";
            $this->data["title"] = "Church Revenue Collection on ". $id .' ('.date("l", strtotime($id)).').' ;
        }elseif($type == 'month' && $id != ''){
            $this->data["week"] = [];
            $this->data["month"] = (int) $id;
            $this->data["title"] = "Church Revenue Collection on "  . date("F", strtotime($id));
        }else{
            $this->data["week"] = [];
            $this->data["month"] = date('m');
            $this->data["title"] = "Church Revenue Collection on " .date('F');
        }

        if($group == 'expense'){
            $this->data['collect'] = Expense::where('id', $id)->first();
            return view('account.expense_report', $this->data);
        }else if($group == 'revenue'){
            $this->data['collect'] = Revenue::where('id', $id)->first();
            return view('account.reports', $this->data);
        }else{
            $this->data['collect'] = [];
        }
        return view('account.reports', $this->data);
    }

    public function receipt()
    {
        $id = request()->segment(3);
        $this->data['receipt'] = Revenue::where('id', $id)->first();
        return view('account.receipt', $this->data);
    }

    public function voucher()
    {
        $id = request()->segment(3);
        $this->data['receipt'] = Expense::where('id', $id)->first();
        return view('account.reports.receipt', $this->data);
    }

    public function loadpage()
        {
            $id = request('id');
            $this->data['methods']  = \App\Models\PaymentMethod::get();
            $this->data['contributions']  = \App\Models\Contribution::get();
            $this->data['envelopes'] = App\Models\Envelope::get();
            $this->data['projects'] =\App\Models\Contribution::where('category_id', 5)->get();
            return view('account.setting.'.$id, $this->data);
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
            return view('account.setting.edit', $this->data);
        }

    public function risiti()
    {
        $id = request('id');
        $this->data['methods']  = \App\Models\PaymentMethod::get();
        return view('account.reports.receipt', $this->data);
    }

    public function groups()
    { 
        $this->data['accounts']  = AccountGroup::get();
        return view('account.charts', $this->data);
    }

    public function requests() {
            $id = request()->segment(3);
            $cash_request_id = request()->segment(4);
            if ($id == 'add') {
                if ($_POST) {
                    $total_amount = 0;
                    foreach (request('requests') as $key => $value) {
                        $total_amount += request('amount')[$key] * request('quantity')[$key];
                    }

                    $object = [
                        'requests' => json_encode(array_merge(request()->except('_token'))),
                        'amount' => $total_amount,
                        'request_date' => date('Y-m-d'),
                        'user_id' => request('user_id'),
                        'method_id' => request('method_id'),
                        'note' => request('note')
                    ];
                    \App\Models\CashRequest::create(array_merge($object, ['verified_by' => Auth::User()->id, 'verify_date' => date('Y-m-d')]));
                    return redirect('accounts/requests')->with('success', 'success');
                }
                $this->data['payments']  = \App\Models\PaymentMethod::get();
                $this->data['users']  = \App\Models\User::get();
                return view('requests/add', $this->data);

            } else if ($id == 'edit') {
                $this->data['requests'] = \App\Models\CashRequest::find($cash_request_id);
                if ($_POST) {
                    $total_amount = 0;
                    foreach (request('requests') as $key => $value) {
                        $total_amount += request('amount')[$key] * request('quantity')[$key];
                    }

                    $object = [
                        'requests' => json_encode(array_merge(request()->except('_token'))),
                        'amount' => $total_amount,
                        'user_id' => request('user_id'),
                        'method_id' => request('method_id'),
                        'note' => request('note')
                    ];
                    $this->data['requests']->update(array_merge($object, ['verified_by' => Auth::User()->id, 'verify_date' => date('Y-m-d')]));
                    return redirect('accounts/requests')->with('success', 'success');
                }
                return view('requests/edit', $this->data);
            } else if ($id == 'view') {
                $this->data['this_request'] = \App\Models\CashRequest::find($cash_request_id);
                return view('requests/view', $this->data);
            } else if ($id == 'delete') {
                \App\Models\CashRequest::find($cash_request_id)->delete();
                return redirect('accounts/requests')->with('success', 'success');
            } else {
           
                $this->data['requests'] = \App\Models\CashRequest::get();
                return view('requests/index', $this->data);
            }
        }

    public function cash_approve() {
        $request = \App\Models\CashRequest::find(request('id'));
        if (!empty($request)) {
            $request->update([request('tag') . '_by' => session('id'),
                request('tag') . '_by_table' => session('table'),
                request('tag') . '_date' => date('Y-m-d')]);
            echo 'Success';
        }
        exit;
    }

}
