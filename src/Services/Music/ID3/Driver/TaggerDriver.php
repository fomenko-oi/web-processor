<?php

declare(strict_types=1);

namespace App\Services\Music\ID3\Driver;

use App\Services\Music\ID3\Song;

interface TaggerDriver
{
    public function handle(string $path, Song $song): void;
}
