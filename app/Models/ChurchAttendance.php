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
class ChurchAttendance extends Model
{
	protected $table = 'chuch_attendance';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'user_id' => 'int',
		'status' => 'int'
	];

    protected $fillable = [
		'user_id',
		'male',
		'female',
		'children',
		'visitor',
		'believer',
		'cars',
		'date',
        'comment',
		'status'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

}
