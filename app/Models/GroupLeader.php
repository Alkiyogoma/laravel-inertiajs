<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class GroupLeader
 * 
 * @property int $id
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property int|null $group_member_id
 * @property int|null $user_id
 * @property int|null $status
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class GroupLeader extends Model
{
	protected $table = 'group_leaders';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'group_member_id' => 'int',
		'role_id' => 'int',
		'status' => 'int'
	];

	protected $dates = [
		'start_date',
		'end_date'
	];

	protected $fillable = [
		'start_date',
		'end_date',
		'group_member_id',
		'role_id',
		'status'
	];

	public function member()
	{
		return $this->belongsTo(GroupMember::class, 'group_member_id', 'id');
	}

	public function role()
	{
		return $this->belongsTo(LeaderRole::class);
	}

}
