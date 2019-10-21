<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Interception\InterceptorInterface;
use Magento\Framework\Phrase;
use Psr\Log\LoggerInterface;

/**
 * Make sure that a request's method can be processed by an action.
 */
class HttpMethodValidator implements ValidatorInterface
{
    /**
     * @var HttpMethodMap
     */
    private $map;

    /**
     * @var LoggerInterface
     */
    private $log;

    /**
     * @param HttpMethodMap $map
     * @param LoggerInterface $logger
     */
    public function __construct(
        HttpMethodMap $map,
        LoggerInterface $logger
    ) {
        $this->map = $map;
        $this->log = $logger;
    }

    /**
     * Create exception when invalid HTTP method used.
     *
     * @param Http $request
     * @param ActionInterface $action
     * @throws InvalidRequestException
     *
     * @return void
     */
    private function throwException(
        Http $request,
        ActionInterface $action
    ): void {
        $uri = $request->getRequestUri();
        $method = $request->getMethod();
        if ($action instanceof InterceptorInterface) {
            $actionClass = get_parent_class($action);
        } else {
            $actionClass = get_class($action);
        }
        $this->log->debug(
            "URI '$uri'' cannot be accessed with $method method ($actionClass)"
        );

        throw new InvalidRequestException(
            new NotFoundException(new Phrase('Page not found.'))
        );
    }

    /**
     * @inheritDoc
     */
    public function validate(
        RequestInterface $request,
        ActionInterface $action
    ): void {
        if ($request instanceof Http) {
            $method = $request->getMethod();
            $map = $this->map->getMap();
            //If we don't have an interface for the HTTP method or
            //the action has HTTP method limitations and doesn't allow the
            //received one then the request is invalid.
            if (!array_key_exists($method, $map)
                || (array_intersect($map, class_implements($action, true))
                    && !$action instanceof $map[$method]
                )
            ) {
                $this->throwException($request, $action);
            }
        }
    }
}
