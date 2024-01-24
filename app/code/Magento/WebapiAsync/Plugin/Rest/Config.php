<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin\Rest;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\Rest\Config as RestConfig;
use Magento\WebapiAsync\Model\ServiceConfig;

/**
 * Overrides the input array size limit for an asynchronous request
 */
class Config
{
    private const KEY_ROUTES = 'routes';

    private const ASYNC_PROCESSOR_PATH = "/\/async\/V\d\//";

    /**
     * @var ServiceConfig
     */
    public $serviceConfig;

    /**
     * @param ServiceConfig $serviceConfig
     */
    public function __construct(ServiceConfig $serviceConfig)
    {
        $this->serviceConfig = $serviceConfig;
    }

    /**
     * Overrides the rules for an asynchronous request
     *
     * @param RestConfig $restConfig
     * @param array $routes
     * @param Request $request
     * @return Route[]
     * @throws InputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetRestRoutes(RestConfig $restConfig, array $routes, Request $request): array
    {
        $httpMethod = $request->getHttpMethod();
        if ($httpMethod === Request::HTTP_METHOD_GET || !$this->canProcess($request)) {
            return $routes;
        }

        $routeConfigs = $this->serviceConfig->getServices()[self::KEY_ROUTES] ?? [];

        /** @var Route $route */
        foreach ($routes as $route) {
            $inputArraySizeLimit = null;
            foreach ($routeConfigs as $routeConfig) {
                if (!isset($routeConfig[$httpMethod])
                    || false === strpos($routeConfig[$httpMethod], $route->getRoutePath())
                    || !isset($routeConfig[RestConfig::KEY_INPUT_ARRAY_SIZE_LIMIT])) {
                    continue;
                }
                $inputArraySizeLimit = $routeConfig[RestConfig::KEY_INPUT_ARRAY_SIZE_LIMIT];
                break;
            }
            $route->setInputArraySizeLimit($inputArraySizeLimit);
        }

        return $routes;
    }

    /**
     * Allow the process if using the asynchronous Webapi
     *
     * @param Request $request
     * @return bool
     */
    private function canProcess(Request $request): bool
    {
        return preg_match(self::ASYNC_PROCESSOR_PATH, $request->getUri()->getPath() ?? '') === 1;
    }
}
