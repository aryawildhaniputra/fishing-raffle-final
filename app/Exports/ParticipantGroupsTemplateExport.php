<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantGroupsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        // Sample data with different status
        return [
            ['Lawang 1', 2, 'L'],
            ['Singosari 5', 3, 'L'],
            ['Malang 10', 5, 'DP'],
            ['Batu 3', 4, 'L'],
            ['Gondanglegi 7', 2, 'BB'],
            ['Kepanjen 2', 1, 'L'],
            ['Pakis 4', 3, 'DP'],
            ['Tumpang 6', 5, 'L'],
            ['Wagir 8', 2, 'L'],
            ['Dau 9', 4, 'BB'],
        ];
    }

    public function headings(): array
    {
        return [
            'name',
            'total_member',
            'status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold header
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA']
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,  // name column
            'B' => 15,  // total_member column
            'C' => 10,  // status column
        ];
    }
}
