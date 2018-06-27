<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin;

use Magento\WebapiAsync\Model\ServiceConfig;
use Magento\Webapi\Controller\PathProcessor;
use Magento\Framework\App\RequestInterface;
use Magento\WebapiAsync\Model\ServiceConfig\Converter;

class ControllerRest
{
    /**
     * @var ServiceConfig
     */
    private $serviceConfig;

    /**
     * @var PathProcessor
     */
    private $pathProcessor;

    /**
     * ControllerRest constructor.
     *
     * @param ServiceConfig $serviceConfig
     * @param PathProcessor $pathProcessor
     */
    public function __construct(
        ServiceConfig $serviceConfig,
        PathProcessor $pathProcessor
    ) {
        $this->serviceConfig = $serviceConfig;
        $this->pathProcessor = $pathProcessor;
    }

    /**
     * Apply route customization.
     * @param \Magento\Webapi\Controller\Rest $subject
     * @param RequestInterface $request
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(\Magento\Webapi\Controller\Rest $subject, RequestInterface $request)
    {
        $routeCustomizations = $this->serviceConfig->getServices()[Converter::KEY_ROUTES] ?? [];
        if ($routeCustomizations) {
            $originPath = $request->getPathInfo();
            $requestMethod = $request->getMethod();
            $routePath = ltrim($this->pathProcessor->process($originPath), '/');
            if (array_key_exists($routePath, $routeCustomizations)) {
                if (isset($routeCustomizations[$routePath][$requestMethod])) {
                    $path = ltrim($routeCustomizations[$routePath][$requestMethod], '/');
                    $request->setPathInfo(str_replace($routePath, $path, $originPath));
                }
            }
        }
        return [$request];
    }
}
