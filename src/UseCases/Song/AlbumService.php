<?php declare(strict_types=1);

namespace App\UseCases\Song;

use App\Requests\Service\Yandex\Download;
use App\Services\Music\Entity\Album\Album;
use App\Services\Music\Entity\Track\Artist;
use App\Services\Music\Entity\Track\Source;
use App\Services\Music\Entity\Track\Track;
use App\Services\Music\ID3\Driver\TaggerDriver;
use App\Services\Music\ID3\Song;
use App\Services\Music\Yandex\Yandex;
use duncan3dc\MetaAudio\Modules\Id3v1;
use duncan3dc\MetaAudio\Modules\Id3v2;
use duncan3dc\MetaAudio\Tagger;
use Psr\Cache\CacheItemPoolInterface;

class AlbumService
{
    const ALBUM_INFO_CACHE_TIME = '+1 day';
    const ALBUM_INFO_CACHE_KEY = 'yandex.album.%d.details';

    /**
     * @var Yandex
     */
    private $yandex;
    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cache;
    /**
     * @var TaggerDriver
     */
    private TaggerDriver $tagger;

    public function __construct(Yandex $yandex, CacheItemPoolInterface $cache, TaggerDriver $tagger)
    {
        // TODO move this to other place
        $yandex->parser->setToken('AgAAAAAUTnpDAAG8XoAqOFtVpkjwqWRB_HKacX0');
        $yandex->downloader->setToken('AgAAAAAGGRgzAAG8Xje4LxlOtEGpu8jkCE8RGY8');

        $this->yandex = $yandex;
        $this->cache = $cache;
    }

    public function getAlbumInfo(int $id): Album
    {
        return $this->cache->get(sprintf(self::ALBUM_INFO_CACHE_KEY, $id), function($item) use($id) {
            $item->expiresAt(new \DateTimeImmutable(self::ALBUM_INFO_CACHE_TIME));

            return $this->yandex->album->getDetails($id);
        });
    }

    public function download(Download $command, $storePath, ?callable $downloadHandler = null): string
    {
        $metaInfo = $this->getTrackInfo($command->id);

        $fileName = $metaInfo->title;

        /** @var Artist $artist */
        if($artist = $metaInfo->artists[0] ?? null) {
            $fileName .= ' - ' . $artist->name;
        }
        /** @var Album $album */
        if($album = $metaInfo->albums[0] ?? null) {
            //$fileName .= ' - ' . $album->title;
        }
        $fileName .= "_{$command->bitrate}";
        $path = "{$storePath}/{$fileName}.mp3";

        if(file_exists($path)) {
            return preg_replace('/.*storage\//', '', $path);
        }

        $sources = $this->getDownloadSources($command->id);

        if(!$sources || count($sources) === 0) {
            throw new \DomainException('Unable to parse song sources.');
        }

        // sort high quality songs first
        usort($sources, function ($a, $b) {return $a->bitrateInKbps < $b->bitrateInKbps;});

        $link = null;

        /** @var Source $source */
        foreach ($sources as $source) {
            if ($source->isMp3() === false) {
                continue;
            }

            if($source->bitrateInKbps !== $command->bitrate) {
                // return the value immediately
                $link = $this->yandex->song->getDirectLink($source->downloadInfoUrl, $source->codec, $source->bitrateInKbps);
                break;
            }
            $link = $this->yandex->song->getDirectLink($source->downloadInfoUrl, $source->codec, $source->bitrateInKbps);
        }

        if(!$link) {
            throw new \DomainException('Unable to parse song download link.');
        }

        $this->yandex->downloadFile($link, $path, $downloadHandler);

        $this->tagger->handle($path, new Song(
            $metaInfo->title,
            $album->title ?? null,
            $artist->name ?? null,
            $album->year ?? null
        ));

        return preg_replace('/.*storage\//', '', $path);
    }

    protected function getId(string $url)
    {
        $segments = explode('/', $url);
        return end($segments);
    }
}
