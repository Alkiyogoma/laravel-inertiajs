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
 * @package App\Models
 */
class Church extends Model
{
	protected $table = 'churchs';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int'
	];

	protected $fillable = [
		'id',
        'name',
        'phone',
		'location',
		'email',
        'location',
        'status'
	];

	public function updates()
	{
		return $this->hasMany(Updates::class, 'church_id', 'id');
	}
}
