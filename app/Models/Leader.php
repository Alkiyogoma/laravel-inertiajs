<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Leader

 * 
 * @property Relation $leaders
 *
 * @package App\Models
 */
class Leader extends Model
{
	protected $table = 'leaders';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'user_id' => 'int',
		'role_id' => 'int',
		'status' => 'int'
	];

	protected $fillable = [
		'user_id',
		'role_id',
		'start_date',
		'end_date',
		'status'
	];

	public function role()
	{
		return $this->belongsTo(LeaderRole::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

}
