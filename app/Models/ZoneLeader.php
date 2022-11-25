<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class zoneLeader
 * 
 * @property int $id
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property int|null $zone_member_id
 * @property int|null $user_id
 * @property int|null $status
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class ZoneLeader extends Model
{
	protected $table = 'zone_leaders';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'zone_id' => 'int',
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
		'zone_id',
		'role_id',
		'user_id',
		'status'
	];

	public function zone()
	{
		return $this->belongsTo(Zone::class, 'zone_id', 'id');
	}

	public function role()
	{
		return $this->belongsTo(LeaderRole::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

}
