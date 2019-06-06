<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook\Tests;

use PHPUnit\Framework\TestCase;
use ShopenGroup\SatisHook\Exception\ParseException;
use ShopenGroup\SatisHook\Request\Gitlab;

abstract class AbstractTest extends TestCase
{
    protected const FILES_PATH = __DIR__ . DIRECTORY_SEPARATOR .
    '..' . DIRECTORY_SEPARATOR .
    'files' . DIRECTORY_SEPARATOR .
    'requests';

    public function testValid(): void
    {
        $gitlabRequest = file_get_contents($this->getRequestPath() . DIRECTORY_SEPARATOR . 'valid-request.json');
        $gitlab = new Gitlab($gitlabRequest);

        $this->assertEquals('git@gitlab.com:shopen-group/modules/module-test-plugin.git', $gitlab->getRepositoryUrl());
    }

    public function testEmpty(): void
    {
        $this->expectException(ParseException::class);
        $this->expectExceptionMessage("Can't parse requestBody - invalid format (empty).");

        new Gitlab('');
    }

    public function testInvalidJson(): void
    {
        $gitlabRequest = file_get_contents($this->getRequestPath() . DIRECTORY_SEPARATOR . 'invalid-json-request.json');

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage("Can't parse requestBody - invalid format (expecting JSON).");

        new Gitlab($gitlabRequest);
    }

    public function testMissingRepositoryUrl(): void
    {
        $gitlabRequest = file_get_contents($this->getRequestPath() . DIRECTORY_SEPARATOR . 'missing-repository-url-request.json');

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage("Can't parse requestBody - missing Repository URL.");

        new Gitlab($gitlabRequest);
    }

    /**
     * @return string
     */
    abstract protected function getRequestPath(): string;
}
