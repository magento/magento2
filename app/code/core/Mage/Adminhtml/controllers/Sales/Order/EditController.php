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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once('CreateController.php');
/**
 * Adminhtml sales order edit controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Sales_Order_EditController extends Mage_Adminhtml_Sales_Order_CreateController
{
    /**
     * Additional initialization
     *
     */
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    /**
     * Start edit order initialization
     */
    public function startAction()
    {
        $this->_getSession()->clear();
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('Mage_Sales_Model_Order')->load($orderId);

        if ($order->getId()) {
            $this->_getSession()->setUseOldShippingMethod(true);
            $this->_getOrderCreateModel()->initFromOrder($order);
            $this->_redirect('*/*');
        }
        else {
            $this->_redirect('*/sales_order/');
        }
    }

    /**
     * Index page
     */
    public function indexAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'))->_title($this->__('Edit Order'));
        $this->loadLayout();

        $this->_initSession()
            ->_setActiveMenu('sales/order')
            ->renderLayout();
    }
    
    /**
     * Acl check for admin
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Admin_Model_Session')->isAllowed('sales/order/actions/edit');
    }    
}
