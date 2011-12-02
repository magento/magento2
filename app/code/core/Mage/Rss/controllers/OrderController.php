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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer reviews controller
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Rss_OrderController extends Mage_Core_Controller_Front_Action
{
    public function newAction()
    {
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function customerAction()
    {
        if (Mage::app()->getStore()->isCurrentlySecure()) {
            $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
            Mage::helper('Mage_Rss_Helper_Data')->authFrontend();
        } else {
            $this->_redirect('rss/order/customer', array('_secure'=>true));
            return $this;
        }
    }

    public function statusAction()
    {
        $decrypt = Mage::helper('Mage_Core_Helper_Data')->decrypt($this->getRequest()->getParam('data'));
        $data = explode(":",$decrypt);
        $oid = (int) $data[0];
        if ($oid) {
            $order = Mage::getModel('Mage_Sales_Model_Order')->load($oid);
            if ($order && $order->getId()) {
                Mage::register('current_order', $order);
                $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
                $this->loadLayout(false);
                $this->renderLayout();
                return;
            }
        }
        $this->_forward('nofeed', 'index', 'rss');
    }

    /**
     * Controller predispatch method to change area for some specific action.
     *
     * @return Mage_Rss_OrderController
     */
    public function preDispatch()
    {
        if ($this->getRequest()->getActionName() == 'new') {
            $this->_currentArea = 'adminhtml';
            Mage::helper('Mage_Rss_Helper_Data')->authAdmin('sales/order');
        }
        return parent::preDispatch();
    }
}
