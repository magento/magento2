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

namespace Magento\Core\Controller\Varien\Router;

class DefaultRouter extends \Magento\Core\Controller\Varien\Router\AbstractRouter
{
    /**
     * @var \Magento\Core\Model\NoRouteHandlerList
     */
    protected $_noRouteHandlerList;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Factory $controllerFactory
     * @param \Magento\Core\Model\NoRouteHandlerList $noRouteHandlerList
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Factory $controllerFactory,
        \Magento\Core\Model\NoRouteHandlerList $noRouteHandlerList
    ) {
        parent::__construct($controllerFactory);
        $this->_noRouteHandlerList = $noRouteHandlerList;
    }

    /**
     * Modify request and set to no-route action
     *
     * @param \Magento\Core\Controller\Request\Http $request
     * @return boolean
     */
    public function match(\Magento\Core\Controller\Request\Http $request)
    {
        foreach ($this->_noRouteHandlerList->getHandlers() as $noRouteHandler) {
            if ($noRouteHandler->process($request)) {
                break;
            }
        }

        return $this->_controllerFactory->createController('Magento\Core\Controller\Varien\Action\Forward',
            array('request' => $request)
        );
    }
}
