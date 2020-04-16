<?php

declare(strict_types=1);

namespace App\Command\Service\Song;

use App\Services\Music\ID3\Driver\TaggerDriver;
use App\Services\Music\ID3\Song;
use App\UseCases\Song\SongService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class SongMetaDataCommand extends Command
{
    protected static $defaultName = 'song:meta';

    /**
     * @var SongService
     */
    private SongService $songService;
    /**
     * @var ContainerBagInterface
     */
    private ContainerBagInterface $containerBag;
    /**
     * @var TaggerDriver
     */
    private TaggerDriver $tagger;

    public function __construct(SongService $songService, ContainerBagInterface $containerBag, TaggerDriver $tagger)
    {
        $this->songService = $songService;
        $this->containerBag = $containerBag;
        $this->tagger = $tagger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Set ID3 meta tags for song.')
            ->addArgument('file', InputArgument::REQUIRED, 'Source file')
            ->addArgument('title', InputArgument::REQUIRED, 'Song ID3 title')
            ->addOption('album', null, InputOption::VALUE_OPTIONAL, 'ID3 Album')
            ->addOption('artist', null, InputOption::VALUE_OPTIONAL, 'ID3 Artist')
            ->addOption('date', null, InputOption::VALUE_OPTIONAL, 'ID3 Year')
            ->addOption('track', null, InputOption::VALUE_OPTIONAL, 'ID3 Track number')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('file');

        if(!file_exists($path)) {
            $output->writeln("<error>File {$path} is'nt exists.</error>");
            return 1;
        }

        try {
            $this->tagger->handle($path, new Song(
                $input->getArgument('title'),
                $input->getOption('album'),
                $input->getOption('artist'),
                (int)$input->getOption('date'),
                (int)$input->getOption('track')
            ));

            $output->writeln("<info>Successful {$path} ID3 tag update.</info>");

            return 0;
        } catch (\Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return 1;
        }
    }
}
