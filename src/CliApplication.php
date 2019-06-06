<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Psr\Log\LoggerInterface;
use ShopenGroup\SatisHook\Command\SyncCommand;
use Symfony;

/**
 * Class CliApplication
 * @package ShopenGroup\SatisHook
 */
class CliApplication implements ApplicationInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $hookFilesPath;

    /**
     * @var Symfony\Component\Console\Application
     */
    private $consoleApplication;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger, string $configPath, string $hookFilesPath)
    {
        $this->logger = $logger;

        if (!is_dir($hookFilesPath) || !is_writable($hookFilesPath)) {
            $msg = "Hook files path error.";
            $this->logger->critical($msg);
            echo $msg;
            exit(1);
        }

        $this->hookFilesPath = $hookFilesPath;

        try {
            $this->config = new \ShopenGroup\SatisHook\Config($configPath);
        } catch (\ShopenGroup\SatisHook\Exception\ConfigException $configException) {
            $this->logger->error($configException->getMessage());
            echo $configException->getMessage();
            exit(1);
        }

        $this->consoleApplication = new Symfony\Component\Console\Application();
        $this->registerCommands();
    }

    public function run(): void
    {
        $this->consoleApplication->run();
    }

    private function registerCommands(): void
    {
        $process = new Process($this->config);
        $this->consoleApplication->add(new SyncCommand($process, $this->hookFilesPath));
    }
}
