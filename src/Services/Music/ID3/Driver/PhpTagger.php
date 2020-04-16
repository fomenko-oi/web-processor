<?php

declare(strict_types=1);

namespace App\Services\Music\ID3\Driver;

use App\Services\Music\ID3\Song;
use duncan3dc\MetaAudio\Modules\Id3v1;
use duncan3dc\MetaAudio\Tagger;

class PhpTagger implements TaggerDriver
{
    public function handle(string $path, Song $song): void
    {
        $tagger = new Tagger();

        $tagger->addDefaultModules();

        $track = $tagger->open($path);

        if($artist = $song->getArtist()) {
            $track->setTitle($artist);
        }
        if($album = $song->getAlbum()) {
            $track->setAlbum($album);
        }
        if($year = $song->getYear()) {
            $track->setYear($year);
        }
        if($number = $song->getNumber()) {
            $track->setTrackNumber($song->getNumber());
        }
        $track->setTitle($song->getTitle());
    }
}
