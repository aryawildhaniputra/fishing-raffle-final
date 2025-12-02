<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'event_date',
        'price',
        'total_registrant',
    ];

    protected $appends = ['created_at_formatted', 'event_date_formatted', 'price_formatted'];

    public function getCreatedAtFormattedAttribute()
    {
        return Carbon::parse($this->created_at)->locale('id')->translatedFormat('d F Y');
    }

    public function getPriceFormattedAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    public function getEventDateFormattedAttribute()
    {
        return Carbon::parse($this->event_date)->locale('id')->translatedFormat('d F Y');
    }

    public function groups(): HasMany
    {
        return $this->hasMany(ParticipantGroup::class, "event_id", "id");
    }

    public function participants()
    {
        return $this->hasManyThrough(Participant::class, ParticipantGroup::class, 'event_id', 'participant_groups_id', 'id', 'id');
    }
}
