<?php

namespace App;

use App\ChannelEnum;
use App\Models\Portal\User;
use App\Models\Missions\Mission;

use GuzzleHttp\Client;
// use RestCord\DiscordClient;
use Illuminate\Support\Facades\Cache;

class Discord
{
    // /**
    //  * The Discord client instance.
    //  *
    //  * @var \RestCord\DiscordClient
    //  */
    // protected static $restcord;

    // private static function Restcord() {
    //     if (self::$restcord) {
    //         return self::$restcord;
    //     }
    //     self::$restcord = new DiscordClient(
    //         ['token' => config('services.discord.token')]
    //     );
    //     return self::$restcord;
    // }

    private static $client;

    private static function Client() {
        if (self::$client) {
            return self::$client;
        }
        self::$client = new Client(
            ['headers' => ['Content-Type' => 'application/json']
        ]);
        return self::$client;
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

    public static function isMember(User $user)
    {
        // return Cache::remember($user->id, 10, function () use ($user) {
        //     $member = Discord::Restcord()->guild->getGuildMember([
        //         'user.id' => (int)$user->discord_id,
        //         'guild.id' => (int)config('services.discord.server_id'),
        //     ]);

        //     return in_array((int)config('services.discord.member_role'), $member->roles);
        // });
        return true;
    }

    public static function isMissionTester(User $user)
    {
        // return Cache::remember($user->id, 10, function () use ($user) {
        //     $member = Discord::Restcord()->guild->getGuildMember([
        //         'user.id' => (int)$user->discord_id,
        //         'guild.id' => (int)config('services.discord.server_id'),
        //     ]);

        //     return in_array((int)config('services.discord.member_role'), $member->roles);
        // });
        return true;
    }
}
