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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customers newsletter subscription controller
 *
 * @category   Mage
 * @package    Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_ManageController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('Mage_Customer_Model_Session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->_initLayoutMessages('Mage_Catalog_Model_Session');

        if ($block = $this->getLayout()->getBlock('customer_newsletter')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }
        $this->getLayout()->getBlock('head')->setTitle($this->__('Newsletter Subscription'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/');
        }
        try {
            Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->setIsSubscribed((boolean)$this->getRequest()->getParam('is_subscribed', false))
            ->save();
            if ((boolean)$this->getRequest()->getParam('is_subscribed', false)) {
                Mage::getSingleton('Mage_Customer_Model_Session')->addSuccess($this->__('We saved the subscription.'));
            } else {
                Mage::getSingleton('Mage_Customer_Model_Session')->addSuccess($this->__('We removed the subscription.'));
            }
        }
        catch (Exception $e) {
            Mage::getSingleton('Mage_Customer_Model_Session')->addError($this->__('Something went wrong while saving your subscription.'));
        }
        $this->_redirect('customer/account/');
    }
}
