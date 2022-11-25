<?php

namespace App\Imports;

use App\Models\Believer;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Auth;

class BeliverImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return Believer|null
     */
    public function model(array $row)
    {
        return new Believer([
           'name' => $row[0],
           'sex'  => $row[1],
           'phone'  => $row[2],
           'email'  => $row[3],
           'status'  => 1,
           'address'  => $row[4],
           'dob'  => $row[5],
           'jod'  => $row[6],
           'martial'  => $row[7],
           'about'  => $row[8],
           'password'  =>  Hash::make($row[2]),
           'user_id' => Auth::User()->id,
           'work_id' => 2
        ]);
    }
}
