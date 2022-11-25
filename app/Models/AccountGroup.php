<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AccountGroup
 * @package App\Models
 */
class AccountGroup extends Model
{
	protected $table = 'account_groups';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'status' => 'int',
	];

	protected $fillable = [
		'id',
		'name',
		'status',
		'start_date'
	];

	public function expenses()
	{
		return $this->hasMany(Expense::class, 'account_group_id', 'id');
    }
    
	public function revenues()
	{
		return $this->hasMany(Revenue::class, 'account_group_id', 'id');
	}

}
