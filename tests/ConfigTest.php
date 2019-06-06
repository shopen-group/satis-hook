<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook\Tests;

use PHPUnit\Framework\TestCase;
use ShopenGroup\SatisHook\Config;
use ShopenGroup\SatisHook\Exception\ConfigException;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = new Config(__DIR__ . '/files/valid.yaml');
    }

    public function testInvalidConfigFilePath(): void
    {
        $this->expectException(ConfigException::class);
        new Config(__DIR__ . '/files/invalid-path.yaml');
    }

    public function testInvalidConfigFileYaml(): void
    {
        $this->expectException(ConfigException::class);
        new Config(__DIR__ . '/files/invalid.yaml');
    }

    public function testInvalidConfigParamValue(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('Location parameter "invalid-location" is not allowed.');
        new Config(__DIR__ . '/files/invalid-param.yaml');
    }

    public function testParamsConfig(): void
    {
        $this->assertArrayHasKey('secret', $this->config->getConfigArray());
        $this->assertArrayHasKey('value', $this->config->getConfigArray()['secret']);
        $this->assertSame($this->config->getConfigArray()['secret']['value'], 'veslo');
    }

    public function testParamsFallbackDefault(): void
    {
        $this->assertArrayHasKey('satis', $this->config->getConfigArray());
        $this->assertArrayHasKey('php', $this->config->getConfigArray()['satis']);
        $this->assertSame($this->config->getConfigArray()['satis']['php'], '/usr/bin/php');
    }
}
