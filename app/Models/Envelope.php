<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 * 
 * @property Collection|EventAttendee[] $event_attendees
 * @property Collection|Group[] $groups
 *
 * @package App\Models
 */

class Envelope extends Model
{
	protected $table = 'envelope';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int'
	];


	protected $fillable = [
		'id',
		'name',
		'year',
		'status'
	];

	public function users()
	{
		return $this->hasMany(UserEnvelope::class);
	}

	public function contributions()
	{
		return $this->hasMany(EnvelopeContribution::class);
	}
}
