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

class EnvelopeContribution extends Model
{
	protected $table = 'envelope_contributions';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int'
	];


	protected $fillable = [
		'id',
		'envelope_id',
		'contribution_id'
	];

	public function envelope()
	{
		return $this->belongsTo(Envelope::class, 'envelope_id', 'id');
	}

	public function contribution()
	{
		return $this->belongsTo(Contribution::class, 'contribution_id', 'id');
	}
}
