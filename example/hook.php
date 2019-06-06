<?php declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use ShopenGroup\SatisHook\ApplicationFactory;

$application = ApplicationFactory::createApplication(__DIR__ . '/config.yaml', __DIR__ . '/temp', __DIR__ . '/logs', $argc);
$application->run();