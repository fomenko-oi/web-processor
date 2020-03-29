<?php

namespace App\Services\Music\Request;

use App\Services\Music\Common\AbstractClient;
use App\Services\Music\Entity\Track\Source;
use App\Services\Music\Entity\Track\Track;
use App\Services\Music\Yandex\BaseClient;
use GuzzleHttp\RequestOptions;

class Song
{
    /**
     * @var BaseClient
     */
    private $client;

    public function __construct(AbstractClient $client)
    {
        $this->client = $client;
    }

    public function getSoundInfo(int $id): Track
    {
        $data = $this->client->get("tracks/{$id}");

        return Track::fromRequest($data['result'][0]);
    }

    // TODO write this method
    public function getSupp()
    {
        $res = $this->client->getClient()->get(self::BASE_URL . '/tracks/' . $id . '/supplement', [
            RequestOptions::HEADERS => [
                'Authorization' => 'OAuth ' . $this->token,
            ],
            RequestOptions::ALLOW_REDIRECTS => 0
        ]);
        //dd(json_decode($res->getBody(), true));
    }

    /**
     * @param int $id
     * @return Source[]
     */
    public function downloadInfo(int $id): array
    {
        $response = $this->client->get("tracks/{$id}/download-info");

        return Source::collection($response['result']);
    }

    public function getDirectLink($url, $codec = 'mp3', $suffix = "1") {
        $response = simplexml_load_string($this->getXml($url));

        $md5 = md5('XGRlBW9FXlekgbPrRHuSiA' . substr($response->path, 1) . $response->s);

        return "https://{$response->host}/get-{$codec}/{$md5}/{$response->ts}{$response->path}";
    }

    public function getXml($url) {
        return $this->client->getClient()->get($url)->getBody();
    }
}
