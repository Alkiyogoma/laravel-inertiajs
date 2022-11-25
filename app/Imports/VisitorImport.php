<?php

namespace App\Imports;

use App\Models\Visitor;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Auth;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VisitorImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        $check = Visitor::where('name', $row['name'])->first();
      if(empty($check)){
        $email =  random_username($row['name']).'@kanisalink.com';
        $pass = '12345678';
        return new Visitor([
            'name' => $row['name'],
            'sex'  => $row['sex'],
            'phone'  => $row['phone'] != '' ? validate_phone_number(trim($row['phone']))[1] : '',
            'email'  => $email,
            'status'  => 1,
            'address'  => $row['address'],
            'dob'  => $row['dob'],
            'jod'  => $row['jod'],
            'martial'  => $row['martial'],
            'about'  => $row['about'],
            'password'  =>  Hash::make($pass),
            'user_id' => Auth::User()->id,
            'work_id' => 2
         ]);
        }else{
            return true;
        }
    }
}