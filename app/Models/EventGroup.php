<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class EventGroup
 * 
 * @property int $id
 * @property int|null $event_id
 * @property int|null $group_id
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Event|null $event
 * @property Group|null $group
 *
 * @package App\Models
 */
class EventGroup extends Model
{
	protected $table = 'event_groups';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'event_id' => 'int',
		'group_id' => 'int'
	];

	protected $fillable = [
		'event_id',
		'group_id'
	];

	public function event()
	{
		return $this->belongsTo(Event::class);
	}

	public function group()
	{
		return $this->belongsTo(Group::class);
	}
}
