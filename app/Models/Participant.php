<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'participant_groups_id',
        'event_id',
        'stall_number',
    ];

    public function participantGroup(): BelongsTo
    {
        return $this->belongsTo(ParticipantGroup::class, 'group_id', 'id');
    }
}
