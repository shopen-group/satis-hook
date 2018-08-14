<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Nette;

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
    public function __construct(Nette\Http\Request $request, Nette\Http\Response $response, string $configPath, string $hookFilesPath)
    {
        $this->request = $request;
        $this->response = $response;

        if (!is_dir($hookFilesPath) || !is_writable($hookFilesPath)) {
            $this->response->setCode(500);
            echo "Hook files path error.";
            exit(1);
        }

        $this->hookFilesPath = $hookFilesPath;

        try {
            $this->config = new \ShopenGroup\SatisHook\Config($configPath);
        } catch (\ShopenGroup\SatisHook\Exception\ConfigException $configException) {
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
        $hook = new \ShopenGroup\SatisHook\Hook($this->config, $this->request, $this->hookFilesPath);

        try {
            $hook->process();
            echo date('[Y-m-d H:i:s]') . ' Hook succeeded.';
        } catch (Exception\SecurityException $securityException) {
            $this->response->setCode($securityException->getCode());
            echo $securityException->getMessage();
            exit(1);
        } catch (Exception\GeneralException $generalException) {
            $this->response->setCode(500);
            echo $generalException->getMessage();
            exit(1);
        }
    }
}
