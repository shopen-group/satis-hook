<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

/**
 * Interface IApplication
 * @package ShopenGroup\SatisHook
 */
interface ApplicationInterface
{
    /**
     * Runs application
     */
    public function run(): void;
}
