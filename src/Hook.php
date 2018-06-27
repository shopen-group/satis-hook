<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Nette\Http\Request;
use ShopenGroup\SatisHook\Exception\GeneralException;
use ShopenGroup\SatisHook\Exception\SecurityException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Hook
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Config
     */
    private $config;

    /**
     * Hook constructor.
     */
    public function __construct(Config $config, Request $request)
    {
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @throws GeneralException
     * @throws SecurityException
     */
    public function process(): void
    {
        if (!$this->isAllowed()) {
            throw new SecurityException('You are not allowed to view this resource.', 401);
        }

        $processArguments = $this->getProcessArguments();
        $process = new Process($processArguments);
        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw new GeneralException($exception->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        // Check HTTP method
        if (!$this->request->isMethod('GET')) {
            return false;
        }

        // Check enabled secret token
        if (!$this->config->getConfig()['secret']['enabled']) {
            return true;
        }

        // Header secret check
        if ($this->config->getConfig()['secret']['location'] === Config::LOCATION_HEADER) {
            $headerKey = $this->request->getHeader($this->config->getConfig()['secret']['name']);
            if ($headerKey === $this->config->getConfig()['secret']['value']) {
                return true;
            }
        }

        // Param query secret check
        if ($this->config->getConfig()['secret']['location'] === Config::LOCATION_PARAM) {
            $paramKey = $this->request->getQuery($this->config->getConfig()['secret']['name']);
            if ($paramKey === $this->config->getConfig()['secret']['value']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function getProcessArguments(): array
    {
        $arguments = [
            $this->config->getConfig()['satis']['php'],
            $this->config->getConfig()['satis']['bin'],
            'build',
            $this->config->getConfig()['satis']['config'],
            $this->config->getConfig()['satis']['output']
        ];

        $repositoryName = $this->request->getQuery('repository');
        if ($repositoryName) {
            $arguments[] = $repositoryName;
        }

        $arguments[] = '-n';

        return $arguments;
    }
}
