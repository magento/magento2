<?php
/**
 * Routes configuration interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
