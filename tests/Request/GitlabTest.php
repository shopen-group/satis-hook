<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook\Tests;

/**
 * Class GitlabTest
 * @package ShopenGroup\SatisHook\Tests
 */
class GitlabTest extends AbstractTest
{
    /**
     * @return string
     */
    protected function getRequestPath(): string
    {
        return self::FILES_PATH . DIRECTORY_SEPARATOR . 'gitlab';
    }
}
