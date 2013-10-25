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
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Rss
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer reviews controller
 */
namespace Magento\Rss\Controller;

class Order extends \Magento\Core\Controller\Front\Action
{
    /**
     * @var \Magento\Config\Scope
     */
    protected $_configScope;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Config\Scope $configScope
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Config\Scope $configScope
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_configScope = $configScope;
        $this->_logger = $context->getLogger();
        parent::__construct($context);
    }

    public function preDispatch()
    {
        if ('new' === $this->getRequest()->getActionName()) {
            $this->_configScope->setCurrentScope(\Magento\Core\Model\App\Area::AREA_ADMINHTML);
            if (!$this->authenticateAndAuthorizeAdmin('Magento_Sales::sales_order', $this->_logger)) {
                return;
            }
        }
        parent::preDispatch();
    }

    public function newAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Order status action
     */
    public function statusAction()
    {
        $order = $this->_objectManager->get('Magento\Rss\Helper\Order')
            ->getOrderByStatusUrlKey((string)$this->getRequest()->getParam('data'));

        if (!is_null($order)) {
            $this->_coreRegistry->register('current_order', $order);
            $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
            $this->loadLayout(false);
            $this->renderLayout();
            return;
        }

        $this->_forward('nofeed', 'index', 'rss');
    }
}
