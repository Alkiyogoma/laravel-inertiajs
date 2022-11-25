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
class Expense extends Model
{
	protected $table = 'expenses';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
        'status' => 'int',
        'account_group_id' => 'int'
	];

	protected $fillable = [
        'user_id',	'account_group_id',	'amount','date', 'method_id','note', 'reference', 'status',	'added_by',	'created_at'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
	
	public function group()
	{
		return $this->belongsTo(AccountGroup::class, 'account_group_id', 'id');
    }
    
	
	public function createdBy()
	{
		return $this->belongsTo(User::class, 'added_by', 'id');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
	}

}
