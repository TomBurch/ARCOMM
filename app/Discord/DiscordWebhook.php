<?php

namespace App\Discord;

use App\Discord\ChannelEnum;
use GuzzleHttp\Client;
use App\Models\Missions\Mission;
use App\Models\Portal\User;
use Google\Cloud\Datastore\DatastoreClient;

class DiscordWebhook
{
    private static $datastore;
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

    private static function Datastore() {
        if (self::$datastore) {
            return self::$datastore;
        }
        self::$datastore = new DatastoreClient([
            'keyFilePath' => config('services.datastore.key_file')       
        ]);
        return self::$datastore;
    }

    public static function missionUpdate(string $content, Mission $mission, bool $tagAuthor = false, string $url = null)
    {
        if ($tagAuthor && ($mission->user->steam_id != auth()->user()->steam_id)) {
            $discordId = self::getDiscordIdFromUser($mission->user);
            if (!is_null($discordId)) {
                $content = "{$content} <@{$discordId}>";
            }
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

    private static function getDiscordIdFromUser(User $user)
    {
        $query = self::Datastore()->query()
        ->kind('DiscordIdentifier')
        ->filter('SteamID', '=', (int)$user->steam_id)
        ->limit(1);
        
        $results = self::Datastore()->runQuery($query);
        $discordId = null;
        foreach ($results as $entity) {
            $discordId = $entity['DiscordID'];
        }
        return $discordId;
    }
}
