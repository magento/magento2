<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route;

/**
 * Routes configuration interface
 *
 * @api
 * @since 2.0.0
 */
interface ConfigInterface
{
    /**
     * Retrieve route front name
     *
     * @param string $routeId
     * @param string $scope
     * @return string
     * @since 2.0.0
     */
    public function getRouteFrontName($routeId, $scope = null);

    /**
     * Get route id by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return string
     * @since 2.0.0
     */
    public function getRouteByFrontName($frontName, $scope = null);

    /**
     * Retrieve list of modules by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return string[]
     * @since 2.0.0
     */
    public function getModulesByFrontName($frontName, $scope = null);
}
