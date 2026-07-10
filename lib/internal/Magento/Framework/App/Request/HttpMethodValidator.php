<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
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
     * @var RawFactory
     */
    private $rawFactory;

    /**
     * @param HttpMethodMap $map
     * @param LoggerInterface $logger
     * @param RawFactory|null $rawFactory
     */
    public function __construct(
        HttpMethodMap $map,
        LoggerInterface $logger,
        ?RawFactory $rawFactory = null
    ) {
        $this->map = $map;
        $this->log = $logger;
        $this->rawFactory = $rawFactory ?: ObjectManager::getInstance()->get(RawFactory::class);
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

        $allowedMethods = [];
        foreach ($this->map->getMap() as $httpMethod => $interface) {
            if ($action instanceof $interface) {
                $allowedMethods[] = $httpMethod;
            }
        }

        if ($allowedMethods) {
            throw new InvalidRequestException(
                $this->rawFactory->create()
                    ->setHttpResponseCode(405)
                    ->setHeader('Allow', implode(', ', $allowedMethods), true)
            );
        }

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
