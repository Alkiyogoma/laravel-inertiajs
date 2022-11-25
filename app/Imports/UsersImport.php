<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return UsersImport|null
     */
    public function model(array $row)
    {
        $email =  random_username($row['name']).'@kanisalink.com';
        $row['dob'] == '' ? $dob = date('Y-d-m') : $dob = $row['dob'];
        $check = User::where('name', $row['name'])->where('phone', validate_phone_number(trim($row['phone']))[1])->first();
        if(empty($check)){
        $user = User::create([
           'name' => $row['name'],
           'sex'  => $row['sex'],
           'phone'  => validate_phone_number(trim($row['phone']))[1],
           'email'  => $email,
           'status'  => 1,
           'address'  => $row['address'],
           'dob'  => $dob,
           'jod'  => $row['jod'],
           'martial'  => $row['martial'],
           'baptized'  => $row['baptized'],
           'zone'  => $row['zone'],
           'about'  => $row['about'],
           'password'  =>  Hash::make($email),
           'role_id' => 8
        ]);
    return $user;

        }else{
            return true;
        }
        /*if($row['family'] == 'family'){
            strtolower($row[1]) == 'female' ? $relation = 1 : $relation = 2;
            $family = \App\Models\Family::create(['user_id' =>  $user->id, 'relation_id' => $relation, 'status' => 1, 'number_id' => $row['number'], 'name' => $user->name]);
        if($family){
                \App\Models\FamilyMember::create(['family_id' => $family->id,'status' => 1, 'user_id' => $user->id, 'relation_id' => $family->relation_id]);
           }
        }elseif($row[family] != ''){
            strtolower($row[1]) == 'female' ? $relation = 4 : $relation = 3;
            $family = \App\Models\Family::where('number_id', $row['number'])->first();
            if(!empty($family)){
                 \App\Models\FamilyMember::create(['family_id' => $family->id,'status' => 1, 'user_id' => $user->id, 'relation_id' => $relation]);
            }
        }
        */
    }
}
