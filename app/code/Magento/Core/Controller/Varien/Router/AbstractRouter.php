<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract router class
 */
namespace Magento\Core\Controller\Varien\Router;

abstract class AbstractRouter
{
    /**
     * @var \Magento\Core\Controller\Varien\Front
     */
    protected $_front;

    /**
     * @var \Magento\Core\Controller\Varien\Action\Factory
     */
    protected $_controllerFactory;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Factory $controllerFactory
     */
    public function __construct(\Magento\Core\Controller\Varien\Action\Factory $controllerFactory)
    {
        $this->_controllerFactory = $controllerFactory;
    }

    /**
     * Assign front controller instance
     *
     * @param $front \Magento\Core\Controller\Varien\Front
     * @return \Magento\Core\Controller\Varien\Router\AbstractRouter
     */
    public function setFront(\Magento\Core\Controller\Varien\Front $front)
    {
        $this->_front = $front;
        return $this;
    }

    /**
     * Retrieve front controller instance
     *
     * @return \Magento\Core\Controller\Varien\Front
     */
    public function getFront()
    {
        return $this->_front;
    }

    /**
     * Retrieve front name by route
     *
     * @param string $routeId
     * @return string
     */
    public function getFrontNameByRoute($routeId)
    {
        return $routeId;
    }

    /**
     * Retrieve route by module front name
     *
     * @param string $frontName
     * @return string
     */
    public function getRouteByFrontName($frontName)
    {
        return $frontName;
    }

    /**
     * Match controller by request
     *
     * @param \Magento\Core\Controller\Request\Http $request
     * @return \Magento\Core\Controller\Varien\Action
     */
    abstract public function match(\Magento\Core\Controller\Request\Http $request);
}
