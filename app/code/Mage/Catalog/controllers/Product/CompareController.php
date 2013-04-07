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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog comapare controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Product_CompareController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * Customer id
     *
     * @var null|int
     */
    protected $_customerId = null;

    public function indexAction()
    {
        $items = $this->getRequest()->getParam('items');

        if ($beforeUrl = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            Mage::getSingleton('Mage_Catalog_Model_Session')
                ->setBeforeCompareUrl(Mage::helper('Mage_Core_Helper_Data')->urlDecode($beforeUrl));
        }

        if ($items) {
            $items = explode(',', $items);
            $list = Mage::getSingleton('Mage_Catalog_Model_Product_Compare_List');
            $list->addProducts($items);
            $this->_redirect('*/*/*');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * Add item to compare list
     */
    public function addAction()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId
            && (Mage::getSingleton('Mage_Log_Model_Visitor')->getId() || Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn())
        ) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if ($product->getId()/* && !$product->isSuper()*/) {
                Mage::getSingleton('Mage_Catalog_Model_Product_Compare_List')->addProduct($product);
                Mage::getSingleton('Mage_Catalog_Model_Session')->addSuccess(
                    $this->__('The product %s has been added to comparison list.', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($product->getName()))
                );
                Mage::dispatchEvent('catalog_product_compare_add_product', array('product'=>$product));
            }

            Mage::helper('Mage_Catalog_Helper_Product_Compare')->calculate();
        }

        $this->_redirectReferer();
    }

    /**
     * Remove item from compare list
     */
    public function removeAction()
    {
        if ($productId = (int) $this->getRequest()->getParam('product')) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);

            if($product->getId()) {
                /** @var $item Mage_Catalog_Model_Product_Compare_Item */
                $item = Mage::getModel('Mage_Catalog_Model_Product_Compare_Item');
                if(Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
                    $item->addCustomerData(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer());
                } elseif ($this->_customerId) {
                    $item->addCustomerData(
                        Mage::getModel('Mage_Customer_Model_Customer')->load($this->_customerId)
                    );
                } else {
                    $item->addVisitorId(Mage::getSingleton('Mage_Log_Model_Visitor')->getId());
                }

                $item->loadByProduct($product);
                /** @var $helper Mage_Catalog_Helper_Product_Compare */
                $helper = Mage::helper('Mage_Catalog_Helper_Product_Compare');
                if($item->getId()) {
                    $item->delete();
                    Mage::getSingleton('Mage_Catalog_Model_Session')->addSuccess(
                        $this->__('The product %s has been removed from comparison list.', $helper->escapeHtml($product->getName()))
                    );
                    Mage::dispatchEvent('catalog_product_compare_remove_product', array('product' => $item));
                    $helper->calculate();
                }
            }
        }

        if (!$this->getRequest()->getParam('isAjax', false)) {
            $this->_redirectReferer();
        }
    }

    /**
     * Remove all items from comparison list
     */
    public function clearAction()
    {
        $items = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Compare_Item_Collection');

        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
            $items->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId());
        } elseif ($this->_customerId) {
            $items->setCustomerId($this->_customerId);
        } else {
            $items->setVisitorId(Mage::getSingleton('Mage_Log_Model_Visitor')->getId());
        }

        /** @var $session Mage_Catalog_Model_Session */
        $session = Mage::getSingleton('Mage_Catalog_Model_Session');

        try {
            $items->clear();
            $session->addSuccess($this->__('The comparison list was cleared.'));
            Mage::helper('Mage_Catalog_Helper_Product_Compare')->calculate();
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, $this->__('An error occurred while clearing comparison list.'));
        }

        $this->_redirectReferer();
    }

    /**
     * Setter for customer id
     *
     * @param int $id
     * @return Mage_Catalog_Product_CompareController
     */
    public function setCustomerId($id)
    {
        $this->_customerId = $id;
        return $this;
    }
}
