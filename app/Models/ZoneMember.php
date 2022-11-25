<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class zoneMember
 * @property Collection|EventAttendee[] $event_attendees
 *
 * @package App\Models
 */
class ZoneMember extends Model
{
	protected $table = 'zone_members';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'status' => 'int',
		'role_id' => 'int'
	];

	protected $dates = [
		'join_date'
	];

	protected $fillable = [
		'zone_id',
		'member_id',
		'join_date',
		'status',
		'role_id'
	];


	public function user()
	{
		return $this->belongsTo(User::class, 'member_id', 'id');
	}

	public function zone()
	{
		return $this->belongsTo(zone::class, 'zone_id', 'id');
	}
	
	public function role()
	{
		return $this->belongsTo(LeaderRole::class, 'role_id', 'id');
	}

	public function leaders()
	{
		return $this->hasMany(ZoneLeader::class, 'id', 'zone_member_id');
	}
}
