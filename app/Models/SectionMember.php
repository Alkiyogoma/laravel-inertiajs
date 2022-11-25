<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class sectionMember
 * @property Collection|EventAttendee[] $event_attendees
 *
 * @package App\Models
 */
class SectionMember extends Model
{
	protected $table = 'section_members';
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
		'section_id',
		'member_id',
		'join_date',
		'status',
		'role_id'
	];


	public function user()
	{
		return $this->belongsTo(User::class, 'member_id', 'id');
	}

	public function section()
	{
		return $this->belongsTo(Section::class, 'section_id', 'id');
	}
	
	public function role()
	{
		return $this->belongsTo(LeaderRole::class, 'role_id', 'id');
	}

	public function leaders()
	{
		return $this->hasMany(SectionLeader::class, 'id', 'section_member_id');
	}
}
