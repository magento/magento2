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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer tags controller
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Tag_CustomerController extends Mage_Core_Controller_Front_Action
{
    protected function _getTagId()
    {
        $tagId = (int) $this->getRequest()->getParam('tagId');
        if ($tagId) {
            $customerId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId();
            $model = Mage::getModel('Mage_Tag_Model_Tag_Relation');
            $model->loadByTagCustomer(null, $tagId, $customerId);
            Mage::register('tagModel', $model);
            return $model->getTagId();
        }
        return false;
    }

    public function indexAction()
    {
        if( !Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn() ) {
            Mage::getSingleton('Mage_Customer_Model_Session')->authenticate($this);
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Tag_Model_Session');
        $this->_initLayoutMessages('Mage_Catalog_Model_Session');

        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('tag/customer');
        }

        $block = $this->getLayout()->getBlock('customer_tags');
        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('Mage_Tag_Helper_Data')->__('My Tags'));
        $this->renderLayout();
    }

    public function viewAction()
    {
        if( !Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn() ) {
            Mage::getSingleton('Mage_Customer_Model_Session')->authenticate($this);
            return;
        }

        $tagId = $this->_getTagId();
        if ($tagId) {
            Mage::register('tagId', $tagId);
            $this->loadLayout();
            $this->_initLayoutMessages('Mage_Tag_Model_Session');

            $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
            if ($navigationBlock) {
                $navigationBlock->setActive('tag/customer');
            }

            $this->_initLayoutMessages('Mage_Checkout_Model_Session');
            $this->getLayout()->getBlock('head')->setTitle(Mage::helper('Mage_Tag_Helper_Data')->__('My Tags'));
            $this->renderLayout();
        }
        else {
            $this->_forward('noRoute');
        }
    }

    public function removeAction()
    {
        if( !Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn() ) {
            Mage::getSingleton('Mage_Customer_Model_Session')->authenticate($this);
            return;
        }

        $tagId = $this->_getTagId();
        if ($tagId) {
            try {
                $model = Mage::registry('tagModel');
                $model->deactivate();

                Mage::getSingleton('Mage_Tag_Model_Session')->addSuccess(
                    Mage::helper('Mage_Tag_Helper_Data')->__('The tag has been deleted.')
                );
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/', array(
                    self::PARAM_NAME_URL_ENCODED => Mage::helper('Mage_Core_Helper_Data')->urlEncode(
                        Mage::getUrl('customer/account/')
                    )
                )));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Tag_Model_Session')->addError(Mage::helper('Mage_Tag_Helper_Data')->__('Unable to remove tag. Please, try again later.'));
            }
        }
        else {
            $this->_forward('noRoute');
        }
    }
}
