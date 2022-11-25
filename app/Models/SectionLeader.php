<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class sectionLeader
 * @package App\Models
 */
class SectionLeader extends Model
{
	protected $table = 'section_leaders';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'section_member_id' => 'int',
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
		'section_member_id',
		'role_id',
		'status'
	];

	public function member()
	{
		return $this->belongsTo(SectionMember::class, 'section_member_id', 'id');
	}

	public function role()
	{
		return $this->belongsTo(LeaderRole::class);
	}

}
