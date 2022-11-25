<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GroupMember
 * @property Collection|EventAttendee[] $event_attendees
 *
 * @package App\Models
 */
class GroupMember extends Model
{
	protected $table = 'group_members';
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
		'group_id',
		'member_id',
		'join_date',
		'status',
		'role_id'
	];


	public function user()
	{
		return $this->belongsTo(User::class, 'member_id', 'id');
	}

	public function group()
	{
		return $this->belongsTo(Group::class, 'group_id', 'id');
	}

	public function event_attendees()
	{
		return $this->hasMany(EventAttendee::class, 'id', 'group_member_id');
	}
	
	public function role()
	{
		return $this->belongsTo(LeaderRole::class, 'role_id', 'id');
	}

	public function leaders()
	{
		return $this->hasMany(GrouPLeader::class, 'id', 'group_member_id');
	}
}
