<?php

namespace App\Models;

use App\Notifications\CustomVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lat',
        'lng',
        'is_active'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Override default Laravel email verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail());
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class, 'user_id');
    }

    // old function for showing all accepted function. need to delete later
    // public function friends()
    // {
    //     return $this->belongsToMany(User::class, 'user_friends', 'user_id', 'friend_id')
    //         ->withPivot('request_status');
    // }

    public function receivedFriendRequests()
    {
        return $this->belongsToMany(User::class, 'user_friends', 'friend_id', 'user_id')
            ->withPivot('request_status');
    }

    public function sentFriendRequests()
    {
        return $this->belongsToMany(User::class, 'user_friends', 'user_id', 'friend_id')
            ->withPivot('request_status');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function groups()
    {
        return $this->belongsToMany(UserGroup::class, 'user_groups', 'user_id', 'group_id');
    }

    // This Function is used in profile pages to show group information that user belongs to
    public function chatGroups()
    {
    return $this->belongsToMany(ChatGroup::class, 'user_groups', 'user_id', 'group_id')
                ->select('chat_groups.id', 'chat_groups.name', 'chat_groups.description');
    }

    public function scopeWithFriendStatus($query, $authUserId)
    {
        return $query->addSelect([
            'is_friend' => DB::table('user_friends')
                ->where(function ($q) use ($authUserId) {
                    $q->where(function ($q1) use ($authUserId) {
                            // Auth user received request from this user
                            $q1->whereColumn('user_friends.user_id', 'users.id')
                            ->where('user_friends.friend_id', $authUserId);
                        })
                    ->orWhere(function ($q2) use ($authUserId) {
                            // Auth user sent request to this user
                            $q2->whereColumn('user_friends.friend_id', 'users.id')
                            ->where('user_friends.user_id', $authUserId);
                        });
                })
                ->where('request_status', 'accepted')
                ->selectRaw('1')
                ->limit(1)
        ]);
    }

}
