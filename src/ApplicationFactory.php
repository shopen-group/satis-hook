<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Class ApplicationFactory
 * @package ShopenGroup\SatisHook
 */
class ApplicationFactory
{
    /**
     * @param string $configPath
     * @param string $hookFilesPath
     * @param string $logsPath
     * @param int|null $argc
     * @return ApplicationInterface
     * @throws \Exception
     */
    public static function createApplication(string $configPath, string $hookFilesPath, string $logsPath, ?int $argc): ApplicationInterface
    {
        if (!is_dir($logsPath) || !is_writable($logsPath)) {
            echo "Logs path error.";
            exit(1);
        }

        $logger = new Logger('satis-hook');
        $logger->pushHandler(new StreamHandler($logsPath . DIRECTORY_SEPARATOR . 'info.log', Logger::INFO, false));
        $logger->pushHandler(new StreamHandler($logsPath . DIRECTORY_SEPARATOR . 'error.log', Logger::WARNING, false));
        if (is_int($argc) && $argc > 1 && php_sapi_name() === 'cli') {
            return new CliApplication($logger, $configPath, $hookFilesPath);
        }

        $response = new \Nette\Http\Response;
        $requestFactory = new \Nette\Http\RequestFactory;
        $requestTypeResolver = new RequestTypeResolver;


        $request = $requestFactory->createHttpRequest();

        return new Application($request, $response, $requestTypeResolver, $logger, $configPath, $hookFilesPath);
    }
}
