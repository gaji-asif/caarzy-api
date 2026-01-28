<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = 'user_groups';


    protected $fillable = [
        'user_id',
        'group_id',
        'request_status',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_groups', 'group_id', 'user_id');
    }
}
