<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use ShopenGroup\SatisHook\Exception\ConfigException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Config
 * @package ShopenGroup\SatisHook
 */
class Config
{
    public const LOCATION_HEADER = 'header';

    public const LOCATION_PARAM = 'param';

    /**
     * @var string
     */
    private $configPath;

    /**
     * @var mixed[]
     */
    private static $configDefaults = [
        'satis' => [
            'php' => '/usr/bin/php',
            'bin' => '../satis/bin/satis',
            'config' => './satis.json',
            'output' => '../web'
        ],
        'secret' => [
            'enabled' => true,
            'location' => self::LOCATION_PARAM,
            'name' => 'key',
            'value' => '54fasf19',
        ]
    ];

    /**
     * @var mixed[]
     */
    private $configArray = [];

    /**
     * Config constructor.
     * @throws ConfigException
     */
    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;

        try {
            $fileConfig = Yaml::parseFile($this->configPath);
        } catch (ParseException $e) {
            throw new ConfigException($e->getMessage());
        }

        $this->build($fileConfig);
        $this->checkParams();
    }

    /**
     * @return string
     */
    public function getConfigFilePath(): string
    {
        return $this->configPath;
    }

    /**
     * @return string[]
     */
    public function getConfigArray(): array
    {
        return $this->configArray;
    }

    /**
     * @return string
     */
    public function getSatisPhpPath(): string
    {
        return $this->resolvePath($this->configArray['satis']['php']);
    }

    /**
     * @return string
     */
    public function getSatisBinPath(): string
    {
        return $this->resolvePath($this->configArray['satis']['bin']);
    }

    /**
     * @return string
     */
    public function getSatisConfigPath(): string
    {
        return $this->resolvePath($this->configArray['satis']['config']);
    }


    /**
     * @return \stdClass
     */
    public function getSatisConfig(): \stdClass
    {
        $configString = utf8_encode((string) file_get_contents($this->getSatisConfigPath()));
        return json_decode($configString);
    }

    /**
     * @param string $repositoryUrl
     * @return bool
     */
    public function isRepositoryInSatisConfig(string $repositoryUrl): bool
    {
        $satisConfig = $this->getSatisConfig();
        if (!isset($satisConfig->repositories)) {
            return false;
        }

        foreach ($satisConfig->repositories as $repository) {
            if ($repository->url === $repositoryUrl) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getSatisOutputPath(): string
    {
        return $this->resolvePath($this->configArray['satis']['output']);
    }

    /**
     * @return bool
     */
    public function isSecretEnabled(): bool
    {
        return (bool) $this->configArray['secret']['enabled'];
    }

    /**
     * @return string
     */
    public function getSecretLocationPath(): string
    {
        return $this->configArray['secret']['location'];
    }

    /**
     * @return string
     */
    public function getSecretName(): string
    {
        return $this->configArray['secret']['name'];
    }

    /**
     * @return string
     */
    public function getSecretValue(): string
    {
        return $this->configArray['secret']['value'];
    }

    /**
     * @param string $path
     * @return string
     */
    private function resolvePath(string $path): string
    {
        // Absolute path check
        if (strpos($path, DIRECTORY_SEPARATOR) === 0) {
            return $path;
        }

        return dirname($this->getConfigFilePath()) . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @param mixed[] $fileConfig
     */
    private function build(array $fileConfig): void
    {
        $this->configArray = array_replace_recursive(
            self::$configDefaults,
            $this->buildIntersect(
                $fileConfig,
                self::$configDefaults
            )
        );
    }

    /**
     * @param mixed[] $master
     * @param mixed[] $compare
     * @return mixed[]
     */
    private function buildIntersect(array $master, array $compare): array
    {
        $master = array_intersect_key($master, $compare);
        foreach ($master as $key => &$value) {
            if (is_array($value)) {
                $value = $this->buildIntersect($value, $compare[$key]);
            }
        }

        return $master;
    }

    /**
     * @throws ConfigException
     */
    private function checkParams(): void
    {
        if ($this->getSecretLocationPath() !== self::LOCATION_PARAM && $this->getSecretLocationPath() !== self::LOCATION_HEADER) {
            throw new ConfigException(sprintf('Location parameter "%s" is not allowed.', $this->getSecretLocationPath()));
        }

        if (!is_file($this->getSatisConfigPath())) {
            throw new ConfigException(sprintf('Satis config file "%s" does not exists.', $this->getSatisConfigPath()));
        }

        if (empty($this->getSatisConfig())) {
            throw new ConfigException(sprintf('Satis config file "%s" contains wrong data.', $this->getSatisConfigPath()));
        }
    }
}
