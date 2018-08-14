<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

/**
 * Class ApplicationFactory
 * @package ShopenGroup\SatisHook
 */
class ApplicationFactory
{
    /**
     * @param string $configPath
     * @param string $hookFilesPath
     * @param int|null $argc
     * @return IApplication
     */
    public static function createApplication(string $configPath, string $hookFilesPath, ?int $argc): IApplication
    {
        if (is_int($argc) && $argc > 1 && php_sapi_name() === 'cli') {
            return new CliApplication($configPath, $hookFilesPath);
        }

        $response = new \Nette\Http\Response;
        $requestFactory = new \Nette\Http\RequestFactory();

        $request = $requestFactory->createHttpRequest();

        return new Application($request, $response, $configPath, $hookFilesPath);
    }
}
