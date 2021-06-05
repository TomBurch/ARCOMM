<?php

namespace App\Discord;

use App\Discord\ChannelEnum;
use GuzzleHttp\Client;
use App\Models\Missions\Mission;
use App\Models\Portal\User;
use Google\Cloud\Datastore\DatastoreClient;

class DiscordWebhook
{
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
        $client = new Client(['headers' => ['Content-Type' => 'application/json']]);
        $webhook = self::getWebhookFromChannel($channel);
        $response = $client->request('POST', $webhook, [
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
        $datastore = new DatastoreClient([
            'keyFilePath' => config('services.datastore.key_file')       
        ]);
        
        $query = $datastore->query()
        ->kind('DiscordIdentifier')
        ->filter('SteamID', '=', (int)$user->steam_id)
        ->limit(1);
        
        $results = $datastore->runQuery($query);
        $discordId = null;
        foreach ($results as $entity) {
            $discordId = $entity['DiscordID'];
        }
        return $discordId;
    }
}
