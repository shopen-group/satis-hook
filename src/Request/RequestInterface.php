<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook\Request;

interface RequestInterface
{
    /**
     * @return string
     */
    public function getRepositoryUrl(): string;
}
