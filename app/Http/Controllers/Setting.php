<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collect;
use App\Models\Envelope;
use Illuminate\Support\Facades\Hash;
use App;
use Auth;
use DB;
use Image;

class Setting extends Home_Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    { 
        $this->data['setting'] = $setting =  DB::table('setting')->first();
        if(empty($setting)){
        return view('setting.create', $this->data);
        }else{
            return view('setting.index', $this->data);
        }

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        if($_POST){
            $filename = '';
            $data = request()->except(['_token','photo']);
            if (!empty(request('photo'))) {
                $file = request()->file('photo');
                $filename = time() . rand(10, 666) . '.' . $file->guessExtension();
               
                $image = Image::make(request()->file('photo'));
                $image->orientate();
                $image->resize(352, 365)->save('storage/uploads/images/' . $filename);
            }
            DB::table('setting')->insert(array_merge($data, ['password' => bcrypt(request('phone')), 'photo' => $filename]));

            //DB::table('setting')->insert(request()->all());
        }
        $this->data['setting'] = DB::table('setting')->first();
        return view('setting.create', $this->data)->with('message', 'New Post created successfully');
    }

    public function edit()
    {
        if($_POST){
            $filename = '';
            $data = request()->except(['_token','photo','source','signature']);
            if (!empty(request('photo'))) {
                $file = request()->file('photo');
                $filename = 'logo' . rand(10, 666) . '.' . $file->guessExtension();
               
                $image = Image::make(request()->file('photo'));
                $image->orientate();
                $image->resize(352, 365)->save('storage/uploads/images/' . $filename);
                DB::table('setting')->update(['photo' => $filename]);
            }
            if (!empty(request('source'))) {
                $source = '';
                $file = request()->file('source');
                $source = 'source' . rand(10, 666) . '.' . $file->guessExtension();
                $image = Image::make(request()->file('source'));
                $image->orientate();
                $image->resize(352, 365)->save('storage/uploads/images/' . $source);
                DB::table('setting')->update(['source' => $source]);
            }
            if (!empty(request('signature'))) {
                $signature = '';
                $file = request()->file('signature');
                $signature = 'signature' . rand(10, 666) . '.' . $file->guessExtension();
                
                $image = Image::make(request()->file('signature'));
                $image->orientate();
                $image->resize(352, 365)->save('storage/uploads/images/' . $signature);
                DB::table('setting')->update(['signature' => $signature]);
            }
            DB::table('setting')->update($data);
            return redirect('setting/index')->with('message', 'Church Settings Updated successfully');

        }
        $this->data['setting'] = DB::table('setting')->first();
        return view('setting.edit', $this->data);
    }


    public function roles()
    {
        if(can_access('manage-users')){
            $this->data['roles'] = \App\Models\Role::get();
            return view('setting.roles', $this->data);
        }else{
            return view('setting.error');
        }
    }

    public function permissions()
    {

        if(can_access('manage-users')){
            $id = request()->segment(3);
            if($_POST){
                $permission = \App\Models\Permission::create(request()->all());
                \App\Models\RoleHasPermission::create(['role_id' => 1, 'permission_id' => $permission->id]);
            }
            $this->data['role_'] =  \App\Models\Role::where('id', $id)->first();
            $this->data['groups'] = \App\Models\PermissionGroup::get();
            return view('setting.permissions', $this->data);
        }else{
            return view('setting.error');
        }
    }

    public function groups()
    {
        if(can_access('manage-users')){;
        return view('setting.roles');
        }else{
            return view('setting.error');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function savePermits()
    {   
        if($_POST){
      
        foreach(request('permission_id') as $value){
            $check  = \App\Models\RoleHasPermission::where('role_id', request('role_id'))->where('permission_id', $value)->first();
            if(empty($check)){
                \App\Models\RoleHasPermission::create(['role_id' => request('role_id'), 'permission_id' => $value]);
            }
        }
        return redirect()->back()->with('message', 'New Post created successfully');
    }
}



    /**
     * Display the specified resource.
     */
    public function report()
    {
        return view('setting.settings');
    }

    public function receipt()
    {
        $id = request()->segment(3);
        $this->data['receipt'] = Collect::where('id', $id)->first();
        return view('giving.receipt', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */

        public function loadpage()
        {
            $id = request('id');
            $this->data['category']  = \App\Models\ContributionCategory::get();
            $this->data['methods']  = \App\Models\PaymentMethod::get();
            $this->data['contributions']  = \App\Models\Contribution::get();
            $this->data['envelopes'] = App\Models\Envelope::get();
            $this->data['projects'] =\App\Models\Contribution::get();
            return view('giving.setting.'.$id, $this->data);
        }

        public function website()
        {
            $id = request()->segment(3);
            $this->data['weeks']  = \App\Models\Weekly::get();
            $this->data['website']  = $website = \App\Models\Website::first();
            $this->data['setting']  = DB::table('setting')->first();
            if($_POST){

                if($id  == 'ibada'){
                    \App\Models\Weekly::create(request()->except('_token'));
                    return redirect()->back()->with('success', 'New Service created successfully');
                }
                if($id  == 'pastor'){
                    !empty($website) ? \App\Models\Website::where('id', $website->id)->update(request()->except(['_token','pastor_photo'])) : \App\Models\Website::create(request()->except(['_token','pastor_photo']));
                    if (!empty(request('pastor_photo'))) {
                        $source = '';
                        $file = request()->file('pastor_photo');
                        $source = 'source' . rand(10, 666) . '.' . $file->guessExtension();
                        $image = Image::make(request()->file('pastor_photo'));
                        $image->orientate();
                        $image->resize(800, 800)->save('storage/uploads/images/' . $source);
                        DB::table('website')->where('id', $website->id)->update(['pastor_photo' => $source]);
                    }
                    return redirect()->back()->with('success', 'Church Website Updated successfully');
                }
                if($id  == 'create'){
                    !empty($website) ? \App\Models\Website::where('id', $website->id)->update(request()->except('_token')) : \App\Models\Website::create(request()->except('_token'));
                    return redirect()->back()->with('success', 'Church Website Updated successfully');
                }
                if($id  == 'mission'){
                    !empty($website) ? \App\Models\Website::where('id', $website->id)->update(request()->except('_token')) : \App\Models\Website::create(request()->except('_token'));
                    return redirect()->back()->with('success', 'Church Website Updated successfully');
                }
            }
            if($id != ''){
                return view('setting.website.'.$id, $this->data);
            }else{
                return view('setting.settings');
            }
        }
    
     
}
