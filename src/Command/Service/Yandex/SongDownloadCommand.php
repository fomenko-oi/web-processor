<?php

declare(strict_types=1);

namespace App\Command\Service\Yandex;

use App\Requests\Service\Yandex\Download;
use App\UseCases\Song\SongService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SongDownloadCommand extends Command
{
    protected static $defaultName = 'song:yandex:download';
    /**
     * @var SongService
     */
    private SongService $songService;
    /**
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $containerBag;

    public function __construct(SongService $songService, ContainerBagInterface $containerBag)
    {
        $this->songService = $songService;

        parent::__construct();
        $this->containerBag = $containerBag;
    }

    protected function configure()
    {
        $this
            ->setDescription('Download song from yandex music by ID')
            ->addArgument('id', InputArgument::REQUIRED, 'Yandex track id')
            ->addArgument('path', InputArgument::REQUIRED, 'Track storage path')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $id = $input->getArgument('id');

        $track = $this->songService->getTrackInfo($id);

        $io->table(['ID', 'Real ID', 'Title', 'Type', 'Size', 'Duration'], [
            [
                $track->id,
                $track->realId,
                $track->title,
                $track->type,
                sprintf('%s mb', $track->getSize('M')),
                sprintf('%s', $track->getDuration()),
            ]
        ]);

        $command = new Download();
        $command->id = (int)$id;
        $command->bitrate = 320;

        $savePath = $this->containerBag->get('app_storage_dir') . '/' . $input->getArgument('path');

        $progressBar = new ProgressBar($output, 100);
        $progressBar->start();

        $prevValue = 0;
        $progressHandler = function($dl_total_size, $dl_size_so_far, $ul_total_size, $ul_size_so_far) use($io, $progressBar, &$prevValue) {
            $percent = $dl_total_size > 0 ? (int)floor($dl_size_so_far / $dl_total_size * 100) : 0;

            if($percent === 0 || $percent === 100) {
                return;
            }

            if($prevValue === $percent) {
                return;
            }

            $progressBar->advance($percent - $prevValue);
            $prevValue = $percent;
        };
        $path = $this->songService->download($command, $savePath, $progressHandler);

        $progressBar->finish();

        $io->newLine(2);
        $io->success("Track saved by path: {$path}");

        return 0;
    }
}
