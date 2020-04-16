<?php

namespace App\Tests;

use App\Services\Music\ID3\Driver\FFMPEGTaggerDriver;
use App\Services\Music\ID3\Song;
use PHPUnit\Framework\TestCase;

class FFMPEGDriverTest extends TestCase
{
    public function testCorrectParamsBuilding()
    {
        $class = new \ReflectionClass(FFMPEGTaggerDriver::class);
        $method = $class->getMethod('buildMetaData');
        $method->setAccessible(true);

        $song = new Song($title = 'test title');
        $data = $method->invokeArgs(new FFMPEGTaggerDriver(), [$song]);

        $this->assertEquals('title="test title"', $data);

        $song = new Song('test title 2', 'test album', 'artist', 2020);
        $data = $method->invokeArgs(new FFMPEGTaggerDriver(), [$song]);
        $this->assertEquals('title="test title 2" album="test album" artist="artist" date="2020"', $data);

        $song = new Song('test title 2', null, null, 2020);
        $data = $method->invokeArgs(new FFMPEGTaggerDriver(), [$song]);
        $this->assertEquals('title="test title 2" date="2020"', $data);
    }
}
