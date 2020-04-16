<?php

declare(strict_types=1);

namespace App\Services\Music\ID3\Driver;

use App\Services\Music\ID3\Song;
use Ramsey\Uuid\Uuid;

class FFMPEGTaggerDriver implements TaggerDriver
{
    public function handle(string $path, Song $song): void
    {
        $outputFile = dirname($path) . '/tmp-' . Uuid::uuid4() . '.mp3';

        exec("ffmpeg -y -i {$path} -metadata {$this->buildMetaData($song)} {$outputFile}", $out, $return);

        if ($return !== 0) {
            throw new \RuntimeException("Unable to set ID3 tags for {$path}.");
        }

        unlink($path);
        rename($outputFile, $path);
    }

    protected function buildMetaData(Song $song): string
    {
        $input = array_filter([
            'title' => $song->getTitle(),
            'album' => $song->getAlbum(),
            'artist' => $song->getArtist(),
            'date' => $song->getYear(),
            'track' => $song->getNumber(),
        ]);

        return implode(' ', array_map(fn($value, $key) => "{$key}=\"$value\"", $input, array_keys($input)));
    }
}
