<?php
/**
 * Router list
 * Used as a container for list of routers
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\App;

use \Magento\App\RouterListInterface;

class RouterList implements RouterListInterface
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * List of routers
     *
     * @var array
     */
    protected $_routerList;

    /**
     * List of active routers objects
     *
     * @var array
     */
    protected $_activeRouters = array();

    /**
     * @param \Magento\ObjectManager $objectManager
     * @param array $routerList
     */
    public function __construct(\Magento\ObjectManager $objectManager, array $routerList)
    {
        $this->_objectManager = $objectManager;
        $this->_routerList = $routerList;
    }

    /**
     * Get list of active routers
     * sorted by sortOrder
     *
     * @return array
     */
    public function getRouters()
    {
        if (empty($this->_activeRouters)) {
            $this->_loadRouters();
        }

        return $this->_activeRouters;
    }

    /**
     * Load active routers
     *
     * @return array
     */
    protected function _loadRouters()
    {
        foreach ($this->_getSortedRouterList() as $routerCode => $routerData) {
            if ((!isset($routerData['disable']) || !$routerData['disable']) && $routerData['instance']) {
                $this->_activeRouters[$routerCode] = $this->_objectManager->create($routerData['instance']);
            }
        }

        return $this->_activeRouters;
    }

    /**
     * Sort routerList by sortOrder
     *
     * @return array
     */
    protected function _getSortedRouterList()
    {
        uasort($this->_routerList, array($this, '_compareRoutersSortOrder'));
        return $this->_routerList;
    }

    /**
     * Compare routers sortOrder
     *
     * @param array $routerData1
     * @param array $routerData2
     * @return int
     */
    protected function _compareRoutersSortOrder($routerData1, $routerData2)
    {
        if ((int)$routerData1['sortOrder'] == (int)$routerData2['sortOrder']) {
            return 0;
        }

        if ((int)$routerData1['sortOrder'] < (int)$routerData2['sortOrder']) {
            return -1;
        } else {
            return 1;
        }
    }

    /**
     * Get router by route
     *
     * @param string $routeId
     * @return \Magento\App\Router\AbstractRouter
     */
    public function getRouterByRoute($routeId)
    {
        $activeRouters = $this->getRouters();
        // empty route supplied - return base url
        if (empty($routeId)) {
            return $activeRouters['standard'];
        }

        if (isset($activeRouters['admin'])) {
            $router = $activeRouters['admin'];
            if ($router->getFrontNameByRoute($routeId)) {
                return $router;
            }
        }

        if (isset($activeRouters['standard'])) {
            $router = $activeRouters['standard'];
            if ($router->getFrontNameByRoute($routeId)) {
                return $router;
            }
        }

        if (isset($activeRouters[$routeId])) {
            $router = $activeRouters[$routeId];
            if ($router->getFrontNameByRoute($routeId)) {
                return $router;
            }
        }

        $router = $activeRouters['default'];;
        return $router;
    }

    /**
     * Get router by frontName
     *
     * @param string $frontName
     * @return \Magento\App\Router\AbstractRouter
     */
    public function getRouterByFrontName($frontName)
    {
        $activeRouters = $this->getRouters();
        // empty route supplied - return base url
        if (empty($frontName)) {
            return $activeRouters['standard'];
        }

        if (isset($activeRouters['admin'])) {
            $router = $activeRouters['admin'];
            if ($router->getRouteByFrontname($frontName)) {
                return $router;
            }
        }

        if (isset($activeRouters['standard'])) {
            $router = $activeRouters['standard'];
            if ($router->getRouteByFrontname($frontName)) {
                return $router;
            }
        }

        $router = $activeRouters['default'];;
        return $router;
    }
}
