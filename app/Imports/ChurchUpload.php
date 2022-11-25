<?php

namespace App\Imports;

use App\Models\Church;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

class ChurchUpload implements ToModel
{
    /**
     * @param array $row
     *
     * @return User|null
     */
    public function model(array $row)
    {
        return new Church([
           'name' => $row[0],
           'location'  => $row[1],
           'phone'  => validate_phone_number(trim($row[2]))[1],
           'status' => 1
        ]);
    }
}