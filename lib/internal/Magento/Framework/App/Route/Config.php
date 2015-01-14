<?php
/**
 * Routes configuration model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Route;

class Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\App\Route\Config\Reader
     */
    protected $_reader;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @var string
     */
    protected $_cacheId;

    /**
     * @var \Magento\Framework\Config\ScopeInterface
     */
    protected $_configScope;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $_areaList;

    /**
     * @var array
     */
    protected $_routes;

    /**
     * @param Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\App\AreaList $areaList
     * @param string $cacheId
     */
    public function __construct(
        Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\App\AreaList $areaList,
        $cacheId = 'RoutesConfig'
    ) {
        $this->_reader = $reader;
        $this->_cache = $cache;
        $this->_cacheId = $cacheId;
        $this->_configScope = $configScope;
        $this->_areaList = $areaList;
    }

    /**
     * Fetch routes from configs by area code and router id
     *
     * @param string $scope
     * @return array
     */
    protected function _getRoutes($scope = null)
    {
        $scope = $scope ?: $this->_configScope->getCurrentScope();
        if (isset($this->_routes[$scope])) {
            return $this->_routes[$scope];
        }
        $cacheId = $scope . '::' . $this->_cacheId;
        $cachedRoutes = unserialize($this->_cache->load($cacheId));
        if (is_array($cachedRoutes)) {
            $this->_routes[$scope] = $cachedRoutes;
            return $cachedRoutes;
        }

        $routers = $this->_reader->read($scope);
        $routes = $routers[$this->_areaList->getDefaultRouter($scope)]['routes'];
        $this->_cache->save(serialize($routes), $cacheId);
        $this->_routes[$scope] = $routes;
        return $routes;
    }

    /**
     * Retrieve route front name
     *
     * @param string $routeId
     * @param null $scope
     * @return string
     */
    public function getRouteFrontName($routeId, $scope = null)
    {
        $routes = $this->_getRoutes($scope);
        return isset($routes[$routeId]) ? $routes[$routeId]['frontName'] : $routeId;
    }

    /**
     * @param string $frontName
     * @param string $scope
     * @return bool|int|string
     */
    public function getRouteByFrontName($frontName, $scope = null)
    {
        foreach ($this->_getRoutes($scope) as $routeId => $routeData) {
            if ($routeData['frontName'] == $frontName) {
                return $routeId;
            }
        }

        return false;
    }

    /**
     * @param string $frontName
     * @param string $scope
     * @return string[]
     */
    public function getModulesByFrontName($frontName, $scope = null)
    {
        $routes = $this->_getRoutes($scope);
        $modules = [];
        foreach ($routes as $routeData) {
            if ($routeData['frontName'] == $frontName && isset($routeData['modules'])) {
                $modules = $routeData['modules'];
                break;
            }
        }

        return array_unique($modules);
    }
}
