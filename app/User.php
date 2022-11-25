<?php

namespace App;
 
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\UserTrait;

class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;
    use UserTrait;

    /**
     * The attributes that are mass assignable.
  
    protected $fillable = [
        'name', 'email', 'sex', 'password',
    ];
   */
    
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
		'email_verified_at',
		'password',
		'remember_token',
		'avatar',
		'role_id'
	];

	public function role()
	{
		return $this->belongsTo(Models\Role::class, 'role_id', 'id');
	}

	public function groups()
	{
		return $this->hasMany(Models\GroupMember::class, 'member_id', 'id');
    }
    

    protected $appends = ['allPermissions', 'profilelink', 'avatarlink', 'isme'];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /*
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAllpermissionsAttribute()
    {   $res = [];
        $allPermissions = $this->getAllPermissions();
        foreach($allPermissions as $p)
        {
            $res[] = $p->name;
        }
        return $res;
    }
}
