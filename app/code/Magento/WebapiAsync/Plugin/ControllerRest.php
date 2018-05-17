<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin;

use Magento\WebapiAsync\Model\RouteCustomizationConfig;
use Magento\Webapi\Controller\PathProcessor;
use Magento\Webapi\Controller\Rest;
use Magento\Framework\App\RequestInterface;

class ControllerRest
{
    /**
     * @var RouteCustomizationConfig
     */
    private $routeCustomizationConfig;

    /**
     * @var PathProcessor
     */
    private $pathProcessor;

    /**
     * ControllerRest constructor.
     *
     * @param RouteCustomizationConfig $routeCustomizationConfig
     * @param PathProcessor $pathProcessor
     */
    public function __construct(
        RouteCustomizationConfig $routeCustomizationConfig,
        PathProcessor $pathProcessor
    )
    {
        $this->routeCustomizationConfig = $routeCustomizationConfig;
        $this->pathProcessor = $pathProcessor;
    }

    /**
     * Check is current rest api route path in route customization config.
     * If that replaces $request route path by related endpoint,
     * @param Rest $subject
     * @param RequestInterface $request
     * @return array
     */
    public function beforeDispatch(Rest $subject, RequestInterface $request)
    {
        $originPath = $request->getPathInfo();
        $routeCustomizations = $this->routeCustomizationConfig->getRouteCustomizations();
        if ($routeCustomizations) {
            $routePath = ltrim($this->pathProcessor->process($originPath), '/');
            if (array_key_exists($routePath, $routeCustomizations)) {
                $endpointPath = ltrim($routeCustomizations[$routePath], '/');
            }
            if (isset($endpointPath)) {
                $request->setPathInfo(str_replace($routePath, $endpointPath, $originPath));
            }
        }
        return [$request];
    }

}