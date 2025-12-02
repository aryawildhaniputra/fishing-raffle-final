<?php

namespace App\Imports;

use App\Models\ParticipantGroup;
use App\Models\Event;
use App\Support\Enums\ParticipantGroupRaffleStatusEnum;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class ParticipantGroupsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    protected $eventId;
    protected $errors = [];
    protected $currentRow = 1; // Start from 1 (header is row 1, data starts at row 2)

    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->currentRow++; // Increment for each row processed
        
        // Check if event exists and has enough quota
        $event = Event::find($this->eventId);
        
        if (!$event) {
            return null;
        }

        $totalMember = (int) $row['total_member'];
        
        // Check quota
        $newTotal = $event->total_registrant + $totalMember;
        if ($newTotal > 222) {
            $this->errors[] = "Baris {$this->currentRow}: Kuota tidak cukup untuk '{$row['name']}' (butuh {$totalMember} slot, tersisa " . (222 - $event->total_registrant) . " slot)";
            return null;
        }

        // Check duplicate name
        $existingGroup = ParticipantGroup::where('event_id', $this->eventId)
            ->where('name', $row['name'])
            ->first();
        
        if ($existingGroup) {
            $this->errors[] = "Baris {$this->currentRow}: Nama '{$row['name']}' sudah digunakan";
            return null;
        }

        // Update event total registrant
        $event->update([
            'total_registrant' => $newTotal
        ]);

        // Map status codes to database values
        $statusMap = [
            'BB' => 'unpaid',
            'DP' => 'dp',
            'L' => 'paid',
        ];
        
        // Get status from Excel, default to 'L' (Lunas) if not provided
        $statusCode = isset($row['status']) && !empty($row['status']) ? strtoupper(trim($row['status'])) : 'L';
        $status = $statusMap[$statusCode] ?? 'paid'; // Default to paid if invalid code

        return new ParticipantGroup([
            'name' => $row['name'],
            'phone_num' => isset($row['phone_num']) && !empty($row['phone_num']) ? $row['phone_num'] : '-',
            'event_id' => $this->eventId,
            'status' => $status,
            'total_member' => $totalMember,
            'raffle_status' => ParticipantGroupRaffleStatusEnum::NOT_YET->value,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'total_member' => 'required|integer|min:1|max:5',
            'status' => 'nullable|in:BB,DP,L,bb,dp,l', // Optional, case-insensitive
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'total_member.required' => 'Total member wajib diisi',
            'total_member.integer' => 'Total member harus berupa angka',
            'total_member.min' => 'Total member minimal 1',
            'total_member.max' => 'Total member maksimal 5',
            'status.in' => 'Status harus BB (Belum Bayar), DP (Down Payment), atau L (Lunas)',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
