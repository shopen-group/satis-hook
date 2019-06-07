<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Nette;
use Psr\Log\LoggerInterface;
use ShopenGroup\SatisHook\Exception\ConfigException;
use ShopenGroup\SatisHook\Exception\GeneralException;

/**
 * Class Application
 * @package ShopenGroup\SatisHook
 */
class Application implements ApplicationInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Nette\Http\IRequest
     */
    private $request;

    /**
     * @var RequestTypeResolver
     */
    private $requestTypeResolver;

    /**
     * @var Nette\Http\IResponse
     */
    private $response;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $hookFilesPath;

    /**
     * Application constructor.
     */
    public function __construct(
        Nette\Http\IRequest $request,
        Nette\Http\IResponse $response,
        RequestTypeResolver $requestTypeResolver,
        LoggerInterface $logger,
        string $configPath,
        string $hookFilesPath
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->requestTypeResolver = $requestTypeResolver;
        $this->logger = $logger;

        if (!is_dir($hookFilesPath) || !is_writable($hookFilesPath)) {
            $this->response->setCode(500);
            echo "Hook files path error.";
            exit(1);
        }

        $this->hookFilesPath = $hookFilesPath;

        try {
            $this->config = new Config($configPath);
        } catch (ConfigException $configException) {
            $this->response->setCode(500);
            echo $configException->getMessage();
            exit(1);
        }
    }

    /**
     * Runs application
     */
    public function run(): void
    {
        $hook = new Hook($this->config, $this->request, $this->requestTypeResolver, $this->hookFilesPath);

        try {
            $hook->process();
            $msg = date('[Y-m-d H:i:s]') . ' Hook succeeded.';

            $this->logger->info($msg);
            echo $msg;
        } catch (GeneralException $e) {
            if ($e->getCode() > 99 && $e->getCode() < 600) {
                $this->response->setCode($e->getCode());
            } else {
                $this->response->setCode(400);
            }

            $msg = get_class($e) . ': ' . $e->getMessage();
            $this->logger->error($msg);
            echo $msg;
            exit(1);
        }
    }
}
