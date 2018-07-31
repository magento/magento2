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
     * @param HttpMethodMap $map
     */
    public function __construct(
        HttpMethodMap $map
    ) {
        $this->map = $map;
    }

    /**
     * @return InvalidRequestException
     */
    private function createException(): InvalidRequestException
    {
        return new InvalidRequestException(
            new NotFoundException(__('Page not found.'))
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
                throw $this->createException();
            }
        }
    }
}
