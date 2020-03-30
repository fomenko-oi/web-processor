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
use Psr\Cache\CacheItemPoolInterface;

class SongService
{
    const TRACK_INFO_CACHE_TIME = '+1 day';
    const TRACK_INFO_CACHE_KEY = 'yandex.track.%d.details';

    /**
     * @var Yandex
     */
    private $yandex;
    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;

    public function __construct(Yandex $yandex, CacheItemPoolInterface $cache)
    {
        // TODO move this to other place
        $yandex->loginByToken('AgAAAAAGGRgzAAG8Xje4LxlOtEGpu8jkCE8RGY8');

        $this->yandex = $yandex;
        $this->cache = $cache;
    }

    public function getTrackInfo($url): Track
    {
        $segments = explode('/', $url);
        $id = end($segments);

        $item = $this->cache->getItem(sprintf(self::TRACK_INFO_CACHE_KEY, $id));

        if(!($data = $item->get())) {
            $item->set($this->yandex->song->getSoundInfo($id))->expiresAt(new \DateTimeImmutable(self::TRACK_INFO_CACHE_TIME));
            $this->cache->save($item);
        }

        return $item->get();
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

    public function download(Download $command, $storePath, ?callable $downloadHandler = null): string
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

        $this->yandex->downloadFile($link, $path = "{$storePath}/{$fileName}.mp3", $downloadHandler);

        $tagger = new Tagger();

        //$tagger->addModule(new Id3v2());
        //$tagger->addModule(new Id3v1());
        //$tagger->addDefaultModules();

        $tagger->open($path)
            ->setArtist($artist->name)
            ->setAlbum($album->title)
            ->setYear($album->year)
            ->setTitle($metaInfo->title)
        ;

        return preg_replace('/.*storage\//', '', $path);
    }

    protected function getId(string $url)
    {
        $segments = explode('/', $url);
        return end($segments);
    }
}
