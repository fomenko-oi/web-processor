<?php

namespace App\UseCases\Song;

use App\Requests\Service\Yandex\Download;
use App\Services\Music\Entity\Track\Album;
use App\Services\Music\Entity\Track\Artist;
use App\Services\Music\Entity\Track\Source;
use App\Services\Music\Entity\Track\Track;
use App\Services\Music\Yandex\Yandex;
use duncan3dc\MetaAudio\Modules\Id3v2;
use duncan3dc\MetaAudio\Tagger;

class SongService
{
    /**
     * @var Yandex
     */
    private $yandex;

    public function __construct(Yandex $yandex)
    {
        // TODO move this to other place
        $yandex->loginByToken('AgAAAAAGGRgzAAG8Xje4LxlOtEGpu8jkCE8RGY8');

        $this->yandex = $yandex;
    }

    public function getTrackInfo($url): Track
    {
        // TODO add cache
        $segments = explode('/', $url);

        return $this->yandex->song->getSoundInfo(end($segments));
    }

    public function getDownloadSources($url)
    {
        return $this->yandex->song->downloadInfo($this->getId($url));
    }

    public function search($query)
    {
        $res = $this->yandex->search('eminem');
        //$res = $this->yandex->search->find('eminem');
        // TODO make search...
    }

    public function download(Download $command, $storePath)
    {
        $sources = $this->getDownloadSources($command->id);

        if(!$sources || count($sources) === 0) {
            throw new \DomainException('Unable to parse song sources.');
        }

        // sort high quality songs first
        usort($sources, function ($a, $b) {return $a->bitrateInKbps < $b->bitrateInKbps;});

        $link = null;

        /** @var Source $source */
        foreach ($sources as $source) {
            if ($source->isMp3() === false || $source->bitrateInKbps !== $command->bitrate) {
                continue;
            }

            $link = $this->yandex->song->getDirectLink($source->downloadInfoUrl, $source->codec, $source->bitrateInKbps);
        }

        if(!$link) {
            throw new \DomainException('Unable to parse song download link.');
        }

        $metaInfo = $this->getTrackInfo($command->id);

        $fileName = $metaInfo->title;

        /** @var Artist $artist */
        if($artist = $metaInfo->artists[0]) {
            $fileName .= ' - ' . $artist->name;
        }
        /** @var Album $album */
        if($album = $metaInfo->albums[0]) {
            //$fileName .= ' - ' . $album->title;
        }

        $this->yandex->downloadFile($link, $path = "{$storePath}/{$fileName}.mp3");

        $tagger = new Tagger();

        $tagger->addModule(new Id3v2());
        //$tagger->addModule(new Id3v1());
        //$tagger->addDefaultModules();

        $tagger->open($path)
            ->setArtist($artist->name)
            ->setAlbum($album->title)
            ->setYear($album->year)
            ->setTitle($metaInfo->title)
        ;

        return $path;
    }

    protected function getId(string $url)
    {
        $segments = explode('/', $url);
        return end($segments);
    }
}
