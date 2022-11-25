<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Relation
 * 
 * @property int $id
 * @property string $name
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Family[] $families
 *
 * @package App\Models
 */
class Relation extends Model
{
	protected $table = 'relation';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'name'
	];

	public function families()
	{
		return $this->hasMany(Family::class);
	}
}
