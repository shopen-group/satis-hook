<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use ShopenGroup\SatisHook\Command\SyncCommand;
use Symfony;

/**
 * Class CliApplication
 * @package ShopenGroup\SatisHook
 */
class CliApplication implements IApplication
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

    public function __construct(string $configPath, string $hookFilesPath)
    {
        if (!is_dir($hookFilesPath) || !is_writable($hookFilesPath)) {
            echo "Hook files path error.";
            exit(1);
        }

        $this->hookFilesPath = $hookFilesPath;

        try {
            $this->config = new \ShopenGroup\SatisHook\Config($configPath);
        } catch (\ShopenGroup\SatisHook\Exception\ConfigException $configException) {
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
