<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class cellLeader
 * 

 *
 * @package App\Models
 */
class CellLeader extends Model
{
	protected $table = 'cell_leaders';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'cell_member_id' => 'int',
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
		'cell_member_id',
		'role_id',
		'status'
	];

	public function member()
	{
		return $this->belongsTo(CellMember::class, 'cell_member_id', 'id');
	}

	public function role()
	{
		return $this->belongsTo(LeaderRole::class);
	}

}
