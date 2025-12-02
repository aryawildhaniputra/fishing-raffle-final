<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParticipantGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'event_id',
        'phone_num',
        'status',
        'raffle_status',
        'stall_order_type',
        'total_member',
        'information',
    ];

    protected $appends = ['created_at_formatted'];

    public function getCreatedAtFormattedAttribute()
    {
        return Carbon::parse($this->created_at)->locale('id')->translatedFormat('d-m-Y');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, "participant_groups_id", "id");
    }
}
