<?php

declare(strict_types=1);

namespace App\Event\Listener\Service\Track;

use App\Entity\Service\Yandex\Track;
use App\Entity\Service\Yandex\Track\Event\TrackCreated;
use App\Infrastructure\Flusher;
use App\Repository\Service\Yandex\SongRepository;
use App\Requests\Service\Yandex\Download;
use App\UseCases\Song\SongService;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DownloadSubscriber implements EventSubscriberInterface
{
    /**
     * @var SongService
     */
    private SongService $song;
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;
    /**
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $bag;
    /**
     * @var SongRepository
     */
    private SongRepository $songs;
    /**
     * @var Flusher
     */
    private Flusher $flusher;

    public function __construct(SongService $songService, ContainerInterface $container, ContainerBagInterface $bag, SongRepository $songs, Flusher $flusher)
    {
        $this->song = $songService;
        $this->container = $container;
        $this->bag = $bag;
        $this->songs = $songs;
        $this->flusher = $flusher;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TrackCreated::class => [['onTrackDownload']],
        ];
    }

    public function onTrackDownload(TrackCreated $event): void
    {
        $command = new Download();
        $command->bitrate = $event->bitrate;
        $command->id = $event->realTrackId;

        /** @var Track $song */
        $track = $this->songs->get($event->trackId);

        $track->toProgress();
        $this->flusher->flush();

        $storageUrl = sprintf($this->bag->get('storage_dir') . '/songs/%d', date('md'));

        if(!file_exists($storageUrl)) {
            mkdir($storageUrl);
        }

        $iteration = 0;
        $downloadHandler = function($dl_total_size, $dl_size_so_far, $ul_total_size, $ul_size_so_far) use(&$iteration, $track) {
            $percent = $dl_total_size > 0 ? (int)floor($dl_size_so_far / $dl_total_size * 100) : 0;

            if($percent === 0 || $percent === 100) {
                return;
            }

            if($iteration % 10 === 0) {
                $track->setProgress($percent);
                $this->flusher->flush();
            }

            $iteration++;
        };

        $path = $this->song->download($command, $storageUrl, $downloadHandler);

        $track->finish(new \DateTimeImmutable(), $path);

        $this->flusher->flush();
    }
}
