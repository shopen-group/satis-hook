<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook\Command;

use Psr\Log\LoggerInterface;
use ShopenGroup\SatisHook\Exception\GeneralException;
use ShopenGroup\SatisHook\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class SyncCommand
 * @package ShopenGroup\SatisHook\Command
 */
class SyncCommand extends Command
{
    /**
     * @var string
     */
    private $hookFilesPath;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SyncCommand constructor.
     */
    public function __construct(Process $process, LoggerInterface $logger, string $hookFilesPath)
    {
        parent::__construct(null);

        $this->process = $process;
        $this->logger = $logger;
        $this->hookFilesPath = $hookFilesPath;
    }

    protected function configure(): void
    {
        $this->setName('satis-hook:build')
            ->setDescription('Builds Satis repository');
    }

    /**
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            '<comment>========================================</comment>',
            '<comment>           SatisHook - Watching         </comment>',
            '<comment>========================================</comment>',
            '',
        ]);

        while (true) {
            $finder = new Finder();
            $finder->files()->in($this->hookFilesPath);

            foreach ($finder as $file) {
                try {
                    $buildStartMsg = 'Starting build ' . $file->getBasename();
                    $this->logger->info($buildStartMsg);
                    $output->writeln(date('Y-m-d h:i:s') . ': ' . $buildStartMsg);

                    $this->process->build((string)$file->getRealPath());

                    $buildFinishMsg = 'Build finished ' . $file->getBasename();
                    $this->logger->info($buildFinishMsg);
                    $output->writeln('<info>' . date('Y-m-d H:i:s') . ': ' . $buildFinishMsg . '</info>');
                } catch (GeneralException $e) {
                    $errorMsg = $e->getMessage() . ' ' . $file->getBasename();
                    $this->logger->error($errorMsg);
                    $output->writeln('<error>' . $errorMsg . '</error>');
                    exit(1);
                }
            }

            sleep(1);
        }
    }
}
