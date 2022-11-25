<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Family

 * 
 * @property Relation $relation
 *
 * @package App\Models
 */
class Family extends Model
{
	protected $table = 'families';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'user_id' => 'int',
		'relation_id' => 'int',
		'status' => 'int'
	];

	protected $fillable = [
		'user_id',
		'name',
		'relation_id',
		'status',
		'number_id'
	];

	public function relation()
	{
		return $this->belongsTo(Relation::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function members()
	{
		return $this->hasMany(FamilyMember::class, 'family_id', 'id');
	}
}
