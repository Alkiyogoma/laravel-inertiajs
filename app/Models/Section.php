<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class section
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
class Section extends Model
{
	protected $table = 'sections';
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
		'zone_id'
	];
	
	public function zone()
	{
		return $this->belongsTo(Zone::class, 'zone_id', 'id');
	}
	
	public function members()
	{
		return $this->hasMany(SectionMember::class, 'section_id', 'id');
	}

}
