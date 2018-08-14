<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

/**
 * Interface IApplication
 * @package ShopenGroup\SatisHook
 */
interface IApplication
{
    /**
     * Runs application
     */
    public function run(): void;
}
