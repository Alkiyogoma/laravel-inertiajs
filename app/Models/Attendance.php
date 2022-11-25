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
class Attendance extends Model
{
	protected $table = 'attendance';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'user_id' => 'int',
		'category_id' => 'int',
		'reason_id' => 'int',
		'added_by' => 'int',
		'status' => 'int'
	];

    protected $fillable = [
		'user_id',
		'category_id',
		'date',
		'reason_id',
        'added_by',
        'category',
		'status'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function added()
	{
		return $this->belongsTo(User::class, 'added_by', 'id');
	}

	public function reason()
	{
		return $this->belongsTo(Reason::class, 'reason_id', 'id');
	}
}
