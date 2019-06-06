<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use ShopenGroup\SatisHook\Exception\GeneralException;
use Symfony;
use Symfony\Component\Process\Exception\ProcessFailedException;

class Process
{

    /**
     * @var Config
     */
    private $config;

    /**
     * Process constructor.
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $filePath
     * @throws GeneralException
     */
    public function build(string $filePath): void
    {
        $fileContent = (string) file_get_contents($filePath);
        $processArguments = $this->getProcessArguments($fileContent);

        $process = new Symfony\Component\Process\Process($processArguments);

        try {
            $process->mustRun();
            unlink($filePath);
        } catch (ProcessFailedException $exception) {
            throw new GeneralException($exception->getMessage());
        }
    }


    /**
     * @return string[]
     */
    private function getProcessArguments(?string $repositoryName): array
    {
        $arguments = [
            $this->config->getSatisPhpPath(),
            $this->config->getSatisBinPath(),
            'build',
            $this->config->getSatisConfigPath(),
            $this->config->getSatisOutputPath()
        ];

        if (!empty($repositoryName)) {
            $arguments[] = '--repository-url';
            $arguments[] = $repositoryName;
        }

        $arguments[] = '-n';
        return $arguments;
    }
}
