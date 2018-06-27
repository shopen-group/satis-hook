<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use ShopenGroup\SatisHook\Exception\ConfigException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Config
{
    public const LOCATION_HEADER = 'header';
    public const LOCATION_PARAM = 'param';

    /**
     * @var mixed[]
     */
    private static $configDefaults = [
        'satis' => [
            'php' => '/usr/bin/php',
            'bin' => '../satis/bin/satis',
            'config' => '../satis.json',
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
        try {
            $fileConfig = Yaml::parseFile($configPath);
        } catch (ParseException $e) {
            throw new ConfigException($e->getMessage());
        }

        $this->build($fileConfig);
        $this->checkParams();
    }

    /**
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return $this->configArray;
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

    private function checkParams(): void
    {
        if ($this->configArray['secret']['location'] !== self::LOCATION_PARAM && $this->configArray['secret']['location'] !== self::LOCATION_HEADER) {
            throw new ConfigException('Location parameter "' . $this->configArray['secret']['location'] . '" is not allowed.');
        }
    }
}
