<?php declare(strict_types=1);

namespace ShopenGroup\SatisHook;

use Nette\Http\IRequest;
use ShopenGroup\SatisHook\Request\Gitlab;
use ShopenGroup\SatisHook\Request\RequestInterface;

/**
 * Class Hook
 * @package ShopenGroup\SatisHook
 */
class RequestTypeResolver
{
    /**
     * Preparation for multi GIT hosting provider support.
     *
     * @throws Exception\ParseException
     */
    public function getRequest(IRequest $request): RequestInterface
    {
        return new Gitlab($request->getRawBody());
    }
}
