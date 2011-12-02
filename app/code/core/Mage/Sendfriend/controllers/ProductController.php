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
 * @package     Mage_Sendfriend
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Email to a Friend Product Controller
 *
 * @category    Mage
 * @package     Mage_Sedfriend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sendfriend_ProductController extends Mage_Core_Controller_Front_Action
{
    /**
     * Predispatch: check is enable module
     * If allow only for customer - redirect to login page
     *
     * @return Mage_Sendfriend_ProductController
     */
    public function preDispatch()
    {
        parent::preDispatch();

        /* @var $helper Mage_Sendfriend_Helper_Data */
        $helper = Mage::helper('Mage_Sendfriend_Helper_Data');
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('Mage_Customer_Model_Session');

        if (!$helper->isEnabled()) {
            $this->norouteAction();
            return $this;
        }

        if (!$helper->isAllowForGuest() && !$session->authenticate($this)) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            if ($this->getRequest()->getActionName() == 'sendemail') {
                $session->setBeforeAuthUrl(Mage::getUrl('*/*/send', array(
                    '_current' => true
                )));
                Mage::getSingleton('Mage_Catalog_Model_Session')
                    ->setSendfriendFormData($this->getRequest()->getPost());
            }
        }

        return $this;
    }

    /**
     * Initialize Product Instance
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $productId  = (int)$this->getRequest()->getParam('id');
        if (!$productId) {
            return false;
        }
        $product = Mage::getModel('Mage_Catalog_Model_Product')
            ->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            return false;
        }

        Mage::register('product', $product);
        return $product;
    }

    /**
     * Initialize send friend model
     *
     * @return Mage_Sendfriend_Model_Sendfriend
     */
    protected function _initSendToFriendModel()
    {
        $model  = Mage::getModel('Mage_Sendfriend_Model_Sendfriend');
        $model->setRemoteAddr(Mage::helper('Mage_Core_Helper_Http')->getRemoteAddr(true));
        $model->setCookie(Mage::app()->getCookie());
        $model->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        Mage::register('send_to_friend_model', $model);

        return $model;
    }

    /**
     * Show Send to a Friend Form
     *
     */
    public function sendAction()
    {
        $product    = $this->_initProduct();
        $model      = $this->_initSendToFriendModel();

        if (!$product) {
            $this->_forward('noRoute');
            return;
        }

        if ($model->getMaxSendsToFriend() && $model->isExceedLimit()) {
            Mage::getSingleton('Mage_Catalog_Model_Session')->addNotice(
                $this->__('The messages cannot be sent more than %d times in an hour', $model->getMaxSendsToFriend())
            );
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Catalog_Model_Session');

        Mage::dispatchEvent('sendfriend_product', array('product' => $product));
        $data = Mage::getSingleton('Mage_Catalog_Model_Session')->getSendfriendFormData();
        if ($data) {
            Mage::getSingleton('Mage_Catalog_Model_Session')->setSendfriendFormData(true);
            $block = $this->getLayout()->getBlock('sendfriend.send');
            if ($block) {
                $block->setFormData($data);
            }
        }

        $this->renderLayout();
    }

    /**
     * Send Email Post Action
     *
     */
    public function sendmailAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/send', array('_current' => true));
        }

        $product    = $this->_initProduct();
        $model      = $this->_initSendToFriendModel();
        $data       = $this->getRequest()->getPost();

        if (!$product || !$data) {
            $this->_forward('noRoute');
            return;
        }

        $categoryId = $this->getRequest()->getParam('cat_id', null);
        if ($categoryId) {
            $category = Mage::getModel('Mage_Catalog_Model_Category')
                ->load($categoryId);
            $product->setCategory($category);
            Mage::register('current_category', $category);
        }

        $model->setSender($this->getRequest()->getPost('sender'));
        $model->setRecipients($this->getRequest()->getPost('recipients'));
        $model->setProduct($product);

        try {
            $validate = $model->validate();
            if ($validate === true) {
                $model->send();
                Mage::getSingleton('Mage_Catalog_Model_Session')->addSuccess($this->__('The link to a friend was sent.'));
                $this->_redirectSuccess($product->getProductUrl());
                return;
            }
            else {
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        Mage::getSingleton('Mage_Catalog_Model_Session')->addError($errorMessage);
                    }
                }
                else {
                    Mage::getSingleton('Mage_Catalog_Model_Session')->addError($this->__('There were some problems with the data.'));
                }
            }
        }
        catch (Mage_Core_Exception $e) {
            Mage::getSingleton('Mage_Catalog_Model_Session')->addError($e->getMessage());
        }
        catch (Exception $e) {
            Mage::getSingleton('Mage_Catalog_Model_Session')
                ->addException($e, $this->__('Some emails were not sent.'));
        }

        // save form data
        Mage::getSingleton('Mage_Catalog_Model_Session')->setSendfriendFormData($data);

        $this->_redirectError(Mage::getURL('*/*/send', array('_current' => true)));
    }
}
