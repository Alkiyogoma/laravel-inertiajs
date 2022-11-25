<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            '#', 'name', 'sex', 'phone', 'address', 'martial', 'zone'
        ];
    }
    public function collection()
    {
        return User::get(['id', 'name', 'sex', 'phone', 'address', 'martial', 'zone']);
    }
}
