<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook\Request;

use ShopenGroup\SatisHook\Exception\ParseException;

/**
 * Class Gitlab
 * @package ShopenGroup\SatisHook\Request
 */
class Gitlab implements RequestInterface
{
    /**
     * @var string
     */
    private $repositoryUrl;

    /**
     * Gitlab constructor.
     * @throws ParseException
     */
    public function __construct(?string $requestBody)
    {
        $this->parseString($requestBody);
    }

    /**
     * @param string|null $requestBody
     * @throws ParseException
     */
    protected function parseString(?string $requestBody): void
    {
        if (empty($requestBody)) {
            throw new ParseException("Can't parse requestBody - invalid format (empty).");
        }

        $bodyJson = json_decode($requestBody, true);
        if ($bodyJson === null) {
            throw new ParseException("Can't parse requestBody - invalid format (expecting JSON).");
        }

        if (!isset($bodyJson['repository']['url'])) {
            throw new ParseException("Can't parse requestBody - missing Repository URL.");
        }

        $this->repositoryUrl = $bodyJson['repository']['url'];
    }

    /**
     * @return string
     */
    public function getRepositoryUrl(): string
    {
        return $this->repositoryUrl;
    }
}
