<?php

namespace App\Services\Music\Entity\Track;

use App\Services\Music\Entity\BaseModel;

class Track extends BaseModel
{
    const DEFAULT_COVER_WIDTH = 300;
    const DEFAULT_COVER_HEIGHT = 300;

    const DEFAULT_OG_WIDTH = 300;
    const DEFAULT_OG_HEIGHT = 300;

    public $id;
    public $realId;
    public $title;
    public $major;
    public $durationMs;
    public $fileSize;
    public $storageDir;
    public $coverUri;
    public $ogImage;
    public $lyricsAvailable;
    public $type;

    /** @var Artist[] */
    public $artists = [];

    /** @var  */
    public $albums = [];

    public function setArtists(array $artists)
    {
        foreach ($artists as $artist) {
            $this->artists[] = Artist::fromRequest($artist);
        }
    }

    public function setAlbums(array $albums)
    {
        foreach ($albums as $album) {
            $this->albums[] = Album::fromRequest($album);
        }
    }

    public function getCoverUrl($width, $height): string
    {
        return sprintf(str_replace('%%', '%sx%s', $this->coverUri), $width, $height);
    }

    public function getOgImage($width, $height): string
    {
        return sprintf(str_replace('%%', '%sx%s', $this->ogImage), $width, $height);
    }

    public function getDuration($seconds = false)
    {
        $ms = $this->durationMs;
        $total_seconds = $ms / 1000;

        if($seconds) {
            return $total_seconds;
        }

        $time = '';

        $value = [
            'hours' => 0,
            'minutes' => 0,
            'seconds' => 0
        ];

        if($total_seconds >= 3600)
        {
            $value['hours'] = floor($total_seconds / 3600);
            $total_seconds = $total_seconds % 3600;

            $time .= $value['hours'] . ':';
        }

        if($total_seconds >= 60)
        {
            $value['minutes'] = floor($total_seconds / 60);
            $total_seconds = $total_seconds % 60;

            $time .= $value['minutes'] . ':';
        } else {
            $time .= '0:';
        }

        $value['seconds'] = floor($total_seconds);

        if($value['seconds'] < 10) {
            $value['seconds'] = '0' . $value['seconds'];
        }

        $time .= $value['seconds'];

        return $time;
    }

    public function getSize($to, $decimal_places = 1)
    {
        $bytes = $this->fileSize;

        $formulas = [
            'K' => number_format($bytes / 1024, $decimal_places),
            'M' => number_format($bytes / 1048576, $decimal_places),
            'G' => number_format($bytes / 1073741824, $decimal_places)
        ];
        return isset($formulas[$to]) ? $formulas[$to] : 0;
    }
}
