<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Nette;
use ShopenGroup\SatisHook\Exception\ConfigException;
use ShopenGroup\SatisHook\Exception\GeneralException;

/**
 * Class Application
 * @package ShopenGroup\SatisHook
 */
class Application implements IApplication
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Nette\Http\Request
     */
    private $request;

    /**
     * @var RequestTypeResolver
     */
    private $requestTypeResolver;

    /**
     * @var Nette\Http\Response
     */
    private $response;

    /**
     * @var string
     */
    private $hookFilesPath;

    /**
     * Application constructor.
     */
    public function __construct(
        Nette\Http\Request $request,
        Nette\Http\Response $response,
        RequestTypeResolver $requestTypeResolver,
        string $configPath,
        string $hookFilesPath
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->requestTypeResolver = $requestTypeResolver;

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
            echo date('[Y-m-d H:i:s]') . ' Hook succeeded.';
        } catch (GeneralException $e) {
            if ($e->getCode() > 0) {
                $this->response->setCode(400);
            } else {
                $this->response->setCode($e->getCode());
            }

            echo get_class($e) . ': ' . $e->getMessage();
            exit(1);
        }
    }
}
