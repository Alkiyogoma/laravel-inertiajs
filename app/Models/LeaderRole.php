<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LeaderRole
 * 
 * @property Collection|EventAttendee[] $event_attendees
 *
 * @package App\Models
 */
class LeaderRole extends Model
{
	protected $table = 'leader_roles';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'name'
	];

	public function event_attendees()
	{
		return $this->hasMany(EventAttendee::class, 'role_id');
	}
}
