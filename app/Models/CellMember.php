<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class cellMember
 * @property Collection|EventAttendee[] $event_attendees
 *
 * @package App\Models
 */
class CellMember extends Model
{
	protected $table = 'cell_members';
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
		'cell_id',
		'member_id',
		'join_date',
		'status',
		'role_id'
	];


	public function user()
	{
		return $this->belongsTo(User::class, 'member_id', 'id');
	}

	public function cell()
	{
		return $this->belongsTo(Cells::class, 'cell_id', 'id');
	}
	
	public function role()
	{
		return $this->belongsTo(LeaderRole::class, 'role_id', 'id');
	}

	public function leaders()
	{
		return $this->hasMany(CellLeader::class, 'id', 'cell_member_id');
	}
}
