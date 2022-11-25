<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CashRequest extends Model {

    /**
     * Generated
     */
    protected $table = 'cash_request';

    protected $fillable = [
        'id', 'amount',
        'user_id',
        'request_date',
        'verify_date',
        'verified_by',
        'approved_by',
        'approved_date',
        'method_id',
        'note',
        'status',
        'created_at',
        'updated_at',
        'requests',
        'reference'
    ];

	protected $casts = [
		'id' => 'int',
		'user_id' => 'int',
		'approved_by' => 'int',
		'verified_by' => 'int',
		'method_id' => 'int'
	];

    public function requestedBy() {
		return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function verifyBy() {
		return $this->belongsTo(User::class, 'verified_by', 'id');
    }

    public function approvedBy() {
		return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class, 'method_id', 'id');
	}

}