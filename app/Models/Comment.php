<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Website
 
 * @property Collection|Event[] $events
 *
 * @package App\Models
 */
class Comment extends Model
{
	protected $table = 'comments';
	public $incrementing = true;

	protected $casts = [
		'id' => 'int',
	];

	protected $fillable = [
        'id','name', 'phone', 'email', 'about', 'status', 'reply', 'created_at', 'updated_at'	
    ];

}
