<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

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
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param HttpMethodMap $map
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        HttpMethodMap $map,
        RedirectFactory $redirectFactory
    ) {
        $this->map = $map;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @return InvalidRequestException
     */
    private function createException(): InvalidRequestException
    {
        $response = $this->redirectFactory->create();
        $response->setHttpResponseCode(302);
        $response->setPath('noroute');

        return new InvalidRequestException($response);
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
