<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 * 
 * @property Collection|EventAttendee[] $event_attendees
 * @property Collection|Group[] $groups
 *
 * @package App\Models
 */

class Collect extends Model
{
	protected $table = 'collections';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'user_id' => 'int',
		'added_by' => 'int',
		'contribution_id' => 'int'
	];


    protected $dates = [
		'date'
    ];
    
	protected $fillable = [
		'id',
		'user_id',
		'contribution_id',
		'amount',
		'method_id',
		'about',
		'date',
		'added_by',
		'reference',
		'phone',
		'payer',
		'status'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
	
	public function addedBy()
	{
		return $this->belongsTo(User::class, 'added_by', 'id');
	}

	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
	}

	public function contribute()
	{
		return $this->belongsTo(Contribution::class, 'contribution_id', 'id');
	}
}


/*

This code is Owned and Managed by Albogast D. Kiyogoma
Phone: +255 744 158 016
Email: albogasty@gmail.com
Email: albogast@albolink.com
Address: P.O Box 75887 Dar es Salaam

*/