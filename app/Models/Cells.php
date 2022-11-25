<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class cell
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
class Cells extends Model
{
	protected $table = 'cells';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'status' => 'int',
		'section_id' => 'int'
	];

	protected $fillable = [
		'id',
		'name',
		'location',
		'status',
		'section_id'
	];

	public function section()
	{
		return $this->belongsTo(Section::class, 'section_id', 'id');
	}
	
	public function members()
	{
		return $this->hasMany(cellMember::class, 'cell_id', 'id');
	}
}
