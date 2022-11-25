<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

	protected $table = 'users';

	protected $casts = [
		'status' => 'int',
		'role_id' => 'int',
		'work_id' => 'int'
	];

	protected $dates = [
		'jod',
		'dob',
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'phone',
		'sex',
		'status',
		'address',
		'martial',
		'baptized',
		'zone',
		'jod',
		'dob',
		'about',
		'password',
		'remember_token',
		'avatar',
		'role_id',
		'work_id'
	];

	public function role()
	{
		return $this->belongsTo(Role::class, 'role_id', 'id');
	}

	public function user_status()
	{
		return $this->belongsTo(UserStatus::class, 'status', 'id');
	}
	public function groups()
	{
		return $this->hasMany(GroupMember::class, 'member_id', 'id');
	}
}
