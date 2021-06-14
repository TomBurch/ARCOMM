<?php

namespace App\Models\Portal;

use Auth;
use Carbon\Carbon;
use App\Discord;
use App\Models\Missions\Mission;
use App\Models\Operations\Absence;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia
{
    use Notifiable;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'discord_id',
        'name',
        'email',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Media library image conversions.
     *
     * @return void
     */
    public function registerMediaConversions(Media $media = null): void {}

    public function hasRole(int $role)
    {
        return Discord::hasRole($this, $role);
    }

    /**
     * Gets the user's missions.
     * Ordered from latest to oldest.
     *
     * @return Collection App\Models\Missions\Mission
     */
    public function missions()
    {
        return Mission::where('user_id', $this->id)->orderBy('created_at', 'desc')->get();
    }

    public static function notInServer()
    {
        //TODO - Find users not in discord server
        return User::all();
    }

    /**
     * Gets all absence announcements for the future.
     *
     * @return Collection App\Models\Operations\Absence
     */
    public function absences()
    {
        return Absence::where('user_id', $this->id)->get()->reject(function($absence) {
            $absence->operation->starts_at >= Carbon::now();
        });
    }
}
