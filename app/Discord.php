<?php

namespace App;

use App\ChannelEnum;
use App\RoleEnum;
use App\Models\Portal\User;
use App\Models\Missions\Mission;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class Discord
{
    private static $client;
    private static $discord;

    private static function Client() 
    {
        if (self::$client) {
            return self::$client;
        }
        self::$client = new Client(
            ['headers' => ['Content-Type' => 'application/json']
        ]);
        return self::$client;
    }

    private static function DiscordClient() 
    {
        if (self::$discord) {
            return self::$discord;
        }
        $botToken = config('services.discord.token');
        self::$discord = new Client(
            ['headers' => ['Authorization' => "Bot {$botToken}"]
        ]);
        return self::$discord;
    }

    public static function missionUpdate(string $content, Mission $mission, bool $tagAuthor = false, string $url = null)
    {
        if ($tagAuthor && ($mission->user->id != auth()->user()->id)) {
            $discordId = auth()->user()->discord_id;
            $content = "{$content} <@{$discordId}>";
        }

        if (!is_null($url)) {
            $content = "{$content}\n{$url}";
        }

        self::notifyChannel(ChannelEnum::Archub, $content);
    }

    public static function notifyChannel(string $channel, string $content)
    {
        $webhook = self::getWebhookFromChannel($channel);
        $response = self::Client()->request('POST', $webhook, [
            'json' => ['content' => $content],
        ]);

        return $response;
    }

    private static function getWebhookFromChannel(int $channel)
    {
        if ($channel == ChannelEnum::Archub) 
        {
            return config('services.discord.archub_webhook');
        }
        else if ($channel == ChannelEnum::Staff)
        {
            return config('services.discord.staff_webhook');
        }
        else
        {
            throw new Exception("Webhook not found");
        }
    }

    public static function isMember(int $discord_id)
    {
        $roleId = self::getRoleIdFromRole(RoleEnum::Member);
        $roles = self::getRoles($discord_id);

        return in_array($roleId, $roles);
    }

    public static function hasRole(User $user, int $role)
    {
        $roleId = self::getRoleIdFromRole($role);
        $roles = self::getRoles($user->discord_id);

        return in_array($roleId, $roles);
    }

    private static function getRoles(int $discord_id)
    {
        return self::getUser($discord_id)["roles"];
    }

    public static function getAvatar(int $discord_id)
    {
        $avatarHash = ((array)self::getUser($discord_id)["user"])["avatar"];
        return "https://cdn.discordapp.com/avatars/{$discord_id}/{$avatarHash}.jpg";
    }

    private static function getRoleIdFromRole(int $role)
    {
        if ($role == RoleEnum::Member)
        {
            return config('services.discord.member_role');
        }
        else if ($role == RoleEnum::Tester)
        {
            return config('services.discord.tester_role');
        }
        else if ($role == RoleEnum::SeniorTester)
        {
            return config('services.discord.senior_tester_role');
        }
        else if ($role == RoleEnum::Staff) 
        {
            return config('services.discord.staff_role');
        }
        else if ($role == RoleEnum::Admin)
        {
            return config('services.discord.admin_role');
        }
        else
        {
            throw new Exception("RoleId not found");
        }
    }

    private static function getUser(int $discord_id)
    {
        return Cache::remember($discord_id, 10, function() use ($discord_id) {
            $guildId = config('services.discord.server_id');
            $url = "https://discord.com/api/v8/guilds/{$guildId}/members/{$discord_id}";
            $response = self::DiscordClient(['exceptions' => false])->request('GET', $url, ['exceptions' => false]);
            $status = $response->getStatusCode();
            
            if ($status == 200) {
                return (array)json_decode($response->getBody());
            }
            throw new Exception("Error getting user from discord {$status}");
        });
    }
}
