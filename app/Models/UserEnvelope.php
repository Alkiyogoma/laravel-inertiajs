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

class UserEnvelope extends Model
{
	protected $table = 'user_envelope';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'user_id' => 'int',
		'envelope_id' => 'int'
	];

    protected $dates = [
		'start_date',
		'end_date'
	];
    				
	protected $fillable = [
		'user_id',
		'envelope_id',
		'number',
		'start_date',
		'end_date'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function envelope()
	{
		return $this->belongsTo(Envelope::class, 'envelope_id', 'id');
	}
}
