<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CellAttendance
 * 
 * @package App\Models
 */
class CellAttendance extends Model
{
	protected $table = 'cell_attendance';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
		'user_id' => 'int',
		'cell_id' => 'int',
		'reason_id' => 'int',
		'added_by' => 'int',
		'status' => 'int'
	];

    protected $fillable = [
		'user_id',
		'cell_id',
		'date',
		'reason_id',
        'added_by',
        'comment',
		'status'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
	public function cell()
	{
		return $this->belongsTo(Cells::class, 'cell_id', 'id');
	}

	public function added()
	{
		return $this->belongsTo(User::class, 'added_by', 'id');
	}

	public function reason()
	{
		return $this->belongsTo(Reason::class, 'reason_id', 'id');
	}
}
