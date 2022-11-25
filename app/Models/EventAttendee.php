<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventAttendee
 * 
 * @property int $id
 * @property int $event_id
 * @property int $group_member_id
 * @property int|null $status
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * @property int|null $role_id
 * 
 * @property Event $event
 * @property GroupMember $group_member
 * @property LeaderRole|null $leader_role
 *
 * @package App\Models
 */
class EventAttendee extends Model
{
	protected $table = 'event_attendees';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'event_id' => 'int',
		'group_member_id' => 'int',
		'status' => 'int',
		'role_id' => 'int'
	];

	protected $fillable = [
		'event_id',
		'group_member_id',
		'status',
		'role_id'
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function group_member()
	{
		return $this->belongsTo(GroupMember::class);
	}

	public function leader_role()
	{
		return $this->belongsTo(LeaderRole::class, 'role_id', 'id');
	}
}
