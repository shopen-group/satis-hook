<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Nette\Http\Request;
use ShopenGroup\SatisHook\Exception\ConfigException;
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
     * @var RequestTypeResolver
     */
    private $requestTypeResolver;

    /**
     * @var string
     */
    private $hookFilesPath;

    /**
     * Hook constructor.
     */
    public function __construct(Config $config, Request $request, RequestTypeResolver $requestTypeResolver, string $hookFilesPath)
    {
        $this->request = $request;
        $this->config = $config;
        $this->requestTypeResolver = $requestTypeResolver;
        $this->hookFilesPath = $hookFilesPath;
    }

    public function process(): void
    {
        if (!$this->isAllowed()) {
            throw new SecurityException('You are not allowed to view this resource.', 401);
        }

        $dateTime = new \DateTime();

        $filename = $this->hookFilesPath . '/' . $dateTime->format('Y-m-d-H-i-s') . '.' . uniqid() . '.req';

        $buildAll = $this->request->getQuery('build-all');

        if ($buildAll !== null) {
            touch($filename);
        } else {
            $repositoryUrl = $this->requestTypeResolver->getRequest($this->request)->getRepositoryUrl();

            if (!$this->config->isRepositoryInSatisConfig($repositoryUrl)) {
                throw new ConfigException(sprintf('There is no repository with url "%s".', $repositoryUrl), 401);
            }

            file_put_contents($filename, $repositoryUrl);
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
        if (!$this->config->isSecretEnabled()) {
            return true;
        }

        // Header secret check
        if ($this->config->getSecretLocationPath() === Config::LOCATION_HEADER) {
            $headerKey = $this->request->getHeader($this->config->getSecretName());
            if ($headerKey === $this->config->getSecretValue()) {
                return true;
            }
        }

        // Param query secret check
        if ($this->config->getSecretLocationPath() === Config::LOCATION_PARAM) {
            $paramKey = $this->request->getQuery($this->config->getSecretName());
            if ($paramKey === $this->config->getSecretValue()) {
                return true;
            }
        }

        return false;
    }
}
