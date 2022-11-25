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

class Event extends Model
{
	protected $table = 'events';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'status' => 'int',
		'user_id' => 'int',
		'department_id' => 'int'
	];

	protected $dates = [
		'event_date'
	];

	protected $fillable = [
		'name',
		'about',
		'attach',
		'start_time',
		'start_date',
		'end_date',
		'status',
		'user_id',
		'department_id'
	];

	public function event_attendees()
	{
		return $this->hasMany(EventAttendee::class);
	}

	public function groups()
	{
		return $this->belongsToMany(Group::class, 'event_groups')
					->withPivot('id')
					->withTimestamps();
	}

	
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id')->withDefault(['name' => 'Unidentified']);
	}
}
