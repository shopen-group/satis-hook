<?php declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$response = new \Nette\Http\Response;

try {
    $config = new \ShopenGroup\SatisHook\Config(__DIR__ . '/config.yaml');
} catch (\ShopenGroup\SatisHook\Exception\ConfigException $configException) {
    $response->setCode(500);
    echo $configException->getMessage();
    exit(1);
}

$requestFactory = new \Nette\Http\RequestFactory();
$request = $requestFactory->createHttpRequest();

$hook = new \ShopenGroup\SatisHook\Hook($config, $request);

try {
    $hook->process();
    echo date('[Y-m-d H:i:s]') . ' Hook succeeded.';
} catch (\ShopenGroup\SatisHook\Exception\SecurityException $securityException) {
    $response->setCode($securityException->getCode());
    echo $securityException->getMessage();
    exit(1);
} catch (\ShopenGroup\SatisHook\Exception\GeneralException $generalException) {
    $response->setCode(500);
    echo $generalException->getMessage();
    exit(1);
}
