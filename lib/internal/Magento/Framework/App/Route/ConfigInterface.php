<?php
/**
 * Routes configuration interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route;

interface ConfigInterface
{
    /**
     * Retrieve route front name
     *
     * @param string $routeId
     * @param string $scope
     * @return string
     */
    public function getRouteFrontName($routeId, $scope = null);

    /**
     * Get route id by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return string
     */
    public function getRouteByFrontName($frontName, $scope = null);

    /**
     * Retrieve list of modules by route front name
     *
     * @param string $frontName
     * @param string $scope
     * @return array
     */
    public function getModulesByFrontName($frontName, $scope = null);
}
