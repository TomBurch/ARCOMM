<?php

namespace App;

use App\Models\Portal\User;

use GuzzleHttp\Client;
use RestCord\DiscordClient;
use Illuminate\Support\Facades\Cache;

class Discord
{
    /**
     * The Discord client instance.
     *
     * @var \RestCord\DiscordClient
     */
    protected static $restcord;

    private static function Restcord() {
        if (self::$restcord) {
            return self::$restcord;
        }
        self::$restcord = new DiscordClient(
            ['token' => config('services.discord.token')]
        );
        return self::$restcord;
    }

    public static function notifyArchub(string $content)
    {
        $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
        $response = $client->request('POST', config('services.discord.archub_webhook'), [
            'json' => ['content' => $content],
        ]);
    }

    public static function notifyStaff(string $content)
    {
        $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
        $response = $client->request('POST', config('services.discord.staff_webhook'), [
            'json' => ['content' => $content],
        ]);
    }

    public static function isMember(User $user)
    {
        return Cache::remember($user->id, 10, function () use ($user) {
            $member = Discord::Restcord()->guild->getGuildMember([
                'user.id' => (int)$user->discord_id,
                'guild.id' => (int)config('services.discord.server_id'),
            ]);

            return in_array((int)config('services.discord.member_role'), $member->roles);
        });
    }
}
