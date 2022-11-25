<?php

namespace App\Exports;

use App\Models\Visitor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VisitorExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            '#', 'name', 'sex', 'phone', 'address', 'martial', 'about', 'date'
        ];
    }
    public function collection()
    {
        return Visitor::get(['id', 'name', 'sex', 'phone', 'address', 'martial', 'about', 'jod']);
    }
}
