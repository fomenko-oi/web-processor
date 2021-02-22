<?php

namespace App\Services\Music\Request;

use App\Services\Music\Common\AbstractClient;
use App\Services\Music\Entity\Track\Lyrics;
use App\Services\Music\Entity\Track\Source;
use App\Services\Music\Entity\Track\Track;
use App\Services\Music\Yandex\BaseClient;
use App\Services\Music\Yandex\Yandex;
use App\UseCases\Song\SongService;
use GuzzleHttp\RequestOptions;

class Song
{
    /**
     * @var SongService
     */
    private Yandex $parent;

    public function __construct(Yandex $parent)
    {
        $this->parent = $parent;
    }

    public function getSoundInfo(int $id): Track
    {
        $data = $this->parent->parser->get("tracks/{$id}");

        return Track::fromRequest($data['result'][0]);
    }

    public function getTrackLyrics(int $id): Lyrics
    {
        $data = $this->parent->parser->get("tracks/{$id}/supplement");

        return Lyrics::fromRequest($data['result']['lyrics']);
    }

    public function getSupplement(int $trackId): array
    {
        return $this->parent->parser->get("/tracks/{$trackId}/supplement");
    }

    /**
     * @param int $id
     * @return Source[]
     */
    public function downloadInfo(int $id): array
    {
        $response = $this->parent->downloader->get("tracks/{$id}/download-info");

        return Source::collection($response['result']);
    }

    public function getDirectLink($url, $codec = 'mp3', $suffix = "1")
    {
        $response = simplexml_load_string($this->getXml($url));

        $md5 = md5('XGRlBW9FXlekgbPrRHuSiA' . substr($response->path, 1) . $response->s);

        return "https://{$response->host}/get-{$codec}/{$md5}/{$response->ts}{$response->path}";
    }

    public function getXml($url)
    {
        return $this->parent->downloader->getClient()->get($url)->getBody();
    }
}
