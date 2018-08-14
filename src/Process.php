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
            $this->config->getConfig()['satis']['php'],
            $this->config->getConfig()['satis']['bin'],
            'build',
            $this->config->getConfig()['satis']['config'],
            $this->config->getConfig()['satis']['output']
        ];

        if (!empty($repositoryName)) {
            $arguments[] = $repositoryName;
        }

        $arguments[] = '-n';
        return $arguments;
    }
}
