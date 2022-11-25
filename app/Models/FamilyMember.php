<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FamilyMember
 * @package App\Models
 */
class FamilyMember extends Model
{
	protected $table = 'family_members';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'family_id' => 'int',
		'user_id' => 'int',
		'relation_id' => 'int',
		'status' => 'int'
	];

	protected $fillable = [
		'family_id',
		'user_id',
		'relation_id',
		'status'
	];
	
	public function family()
	{
		return $this->belongsTo(Family::class, 'family_id', 'id');
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
	
	public function relation()
	{
		return $this->belongsTo(Relation::class, 'relation_id', 'id');
	}
}
