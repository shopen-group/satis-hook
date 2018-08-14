<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Nette\Http\Request;
use ShopenGroup\SatisHook\Exception\SecurityException;

/**
 * Class Hook
 * @package ShopenGroup\SatisHook
 */
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
     * @var string
     */
    private $hookFilesPath;

    /**
     * Hook constructor.
     */
    public function __construct(Config $config, Request $request, string $hookFilesPath)
    {
        $this->request = $request;
        $this->config = $config;
        $this->hookFilesPath = $hookFilesPath;
    }

    public function process(): void
    {
        if (!$this->isAllowed()) {
            throw new SecurityException('You are not allowed to view this resource.', 401);
        }

        $dateTime = new \DateTime();

        $filename = $this->hookFilesPath . '/' . $dateTime->format('Y-m-d-H-i-s') . '.' . uniqid() . '.req';

        $repositoryName = $this->request->getQuery('repository');
        if ($repositoryName) {
            file_put_contents($filename, $repositoryName);
        } else {
            touch($filename);
        }
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        // Check HTTP method
        if (!$this->request->isMethod('GET') && !$this->request->isMethod('POST')) {
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
}
