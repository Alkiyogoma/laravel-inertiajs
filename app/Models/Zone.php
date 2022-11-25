<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class zone
 * 
 * @property int $id
 * @property string $name
 * @property string $about
 * @property int|null $status
 * @property int|null $user_id
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Event[] $events
 *
 * @package App\Models
 */
class Zone extends Model
{
	protected $table = 'zones';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'status' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'id',
		'name',
		'location',
		'status',
		'user_id'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
	
	public function leaders()
	{
		return $this->hasMany(ZoneLeader::class, 'zone_id', 'id');
	}

	public function sections()
	{
		return $this->hasMany(Section::class, 'zone_id', 'id');
	}
}
