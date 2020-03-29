<?php

namespace App\Services\Music\Common;

use Stormiix\EyeD3\EyeD3;

class MP3
{
    public $file;
    public $title = "";
    public $artist = "";
    public $album = "";
    public $year = "";
    public $genre = "";

    public function __construct($file)
    {
        $eyed3 = new EyeD3($file);
        $tags = $eyed3->readMeta();
        $this->file = $file;
        $title = array_key_exists("title", $tags) ? $tags["title"] : basename($file, ".mp3");
        $data = explode('-', $title);
        $this->title = array_key_exists(1, $data) ? trim($data[1]) : $tags["title"] ?? '';
        $this->artist = array_key_exists(1, $data) ? trim($data[0]) : $tags["artist"] ?? '';
        $this->album = array_key_exists("album", $tags) ? $tags["album"] : array_key_exists("title",
            $tags) ? $this->title . " [Single]" : '';
        $this->year = array_key_exists("year", $tags) ? $tags["year"] : '';
        $this->genre = array_key_exists("genre", $tags) ? $tags["genre"]["genre"] : 'Unknown';
    }

    public function writeTags($tags)
    {
        if ($tags == []) {
            return false;
        } else {
            $eyed3 = new EyeD3($this->file);
            $eyed3->updateMeta($tags);
            return true;
        }
    }
}
