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
 * @package     Mage_ProductAlert
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * ProductAlert Email processor
 *
 * @category   Mage
 * @package    Mage_ProductAlert
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_ProductAlert_Model_Email extends Mage_Core_Model_Abstract
{
    const XML_PATH_EMAIL_PRICE_TEMPLATE = 'catalog/productalert/email_price_template';
    const XML_PATH_EMAIL_STOCK_TEMPLATE = 'catalog/productalert/email_stock_template';
    const XML_PATH_EMAIL_IDENTITY       = 'catalog/productalert/email_identity';

    /**
     * Type
     *
     * @var string
     */
    protected $_type = 'price';

    /**
     * Website Model
     *
     * @var Mage_Core_Model_Website
     */
    protected $_website;

    /**
     * Customer model
     *
     * @var Mage_Customer_Model_Customer
     */
    protected $_customer;

    /**
     * Products collection where changed price
     *
     * @var array
     */
    protected $_priceProducts = array();

    /**
     * Product collection which of back in stock
     *
     * @var array
     */
    protected $_stockProducts = array();

    /**
     * Price block
     *
     * @var Mage_ProductAlert_Block_Email_Price
     */
    protected $_priceBlock;

    /**
     * Stock block
     *
     * @var Mage_ProductAlert_Block_Email_Stock
     */
    protected $_stockBlock;

    /**
     * Set model type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Retrieve model type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set website model
     *
     * @param Mage_Core_Model_Website $website
     * @return Mage_ProductAlert_Model_Email
     */
    public function setWebsite(Mage_Core_Model_Website $website)
    {
        $this->_website = $website;
        return $this;
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return Mage_ProductAlert_Model_Email
     */
    public function setWebsiteId($websiteId)
    {
        $this->_website = Mage::app()->getWebsite($websiteId);
        return $this;
    }

    /**
     * Set customer by id
     *
     * @param int $customerId
     * @return Mage_ProductAlert_Model_Email
     */
    public function setCustomerId($customerId)
    {
        $this->_customer = Mage::getModel('Mage_Customer_Model_Customer')->load($customerId);
        return $this;
    }

    /**
     * Set customer model
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Mage_ProductAlert_Model_Email
     */
    public function setCustomer(Mage_Customer_Model_Customer $customer)
    {
        $this->_customer = $customer;
        return $this;
    }

    /**
     * Clean data
     *
     * @return Mage_ProductAlert_Model_Email
     */
    public function clean()
    {
        $this->_customer      = null;
        $this->_priceProducts = array();
        $this->_stockProducts = array();

        return $this;
    }

    /**
     * Add product (price change) to collection
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_ProductAlert_Model_Email
     */
    public function addPriceProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_priceProducts[$product->getId()] = $product;
        return $this;
    }

    /**
     * Add product (back in stock) to collection
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_ProductAlert_Model_Email
     */
    public function addStockProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_stockProducts[$product->getId()] = $product;
        return $this;
    }

    /**
     * Retrieve price block
     *
     * @return Mage_ProductAlert_Block_Email_Price
     */
    protected function _getPriceBlock()
    {
        if (is_null($this->_priceBlock)) {
            $this->_priceBlock = Mage::helper('Mage_ProductAlert_Helper_Data')
                ->createBlock('Mage_ProductAlert_Block_Email_Price');
        }
        return $this->_priceBlock;
    }

    /**
     * Retrieve stock block
     *
     * @return Mage_ProductAlert_Block_Email_Stock
     */
    protected function _getStockBlock()
    {
        if (is_null($this->_stockBlock)) {
            $this->_stockBlock = Mage::helper('Mage_ProductAlert_Helper_Data')
                ->createBlock('Mage_ProductAlert_Block_Email_Stock');
        }
        return $this->_stockBlock;
    }

    /**
     * Send customer email
     *
     * @return bool
     */
    public function send()
    {
        if (is_null($this->_website) || is_null($this->_customer)) {
            return false;
        }
        if (($this->_type == 'price' && count($this->_priceProducts) == 0)
            || ($this->_type == 'stock' && count($this->_stockProducts) == 0)
        ) {
            return false;
        }
        if (!$this->_website->getDefaultGroup() || !$this->_website->getDefaultGroup()->getDefaultStore()) {
            return false;
        }

        $store      = $this->_website->getDefaultStore();
        $storeId    = $store->getId();

        if ($this->_type == 'price' && !Mage::getStoreConfig(self::XML_PATH_EMAIL_PRICE_TEMPLATE, $storeId)) {
            return false;
        } elseif ($this->_type == 'stock' && !Mage::getStoreConfig(self::XML_PATH_EMAIL_STOCK_TEMPLATE, $storeId)) {
            return false;
        }

        if ($this->_type != 'price' && $this->_type != 'stock') {
            return false;
        }

        $appEmulation = Mage::getSingleton('Mage_Core_Model_App_Emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        if ($this->_type == 'price') {
            $this->_getPriceBlock()
                ->setStore($store)
                ->reset();
            foreach ($this->_priceProducts as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getPriceBlock()->addProduct($product);
            }
            $block = $this->_getPriceBlock()->toHtml();
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_PRICE_TEMPLATE, $storeId);
        } else {
            $this->_getStockBlock()
                ->setStore($store)
                ->reset();
            foreach ($this->_stockProducts as $product) {
                $product->setCustomerGroupId($this->_customer->getGroupId());
                $this->_getStockBlock()->addProduct($product);
            }
            $block = $this->_getStockBlock()->toHtml();
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_STOCK_TEMPLATE, $storeId);
        }

        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        Mage::getModel('Mage_Core_Model_Email_Template')
            ->setDesignConfig(array(
                'area'  => Mage_Core_Model_App_Area::AREA_FRONTEND,
                'store' => $storeId
            ))->sendTransactional(
                $templateId,
                Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId),
                $this->_customer->getEmail(),
                $this->_customer->getName(),
                array(
                    'customerName'  => $this->_customer->getName(),
                    'alertGrid'     => $block
                )
            );

        return true;
    }
}
