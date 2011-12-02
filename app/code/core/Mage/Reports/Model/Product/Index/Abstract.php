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
 * @package     Mage_Reports
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Reports Product Index Abstract Model
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Reports_Model_Product_Index_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * Cache key name for Count of product index
     *
     * @var string
     */
    protected $_countCacheKey;

    /**
     * Prepare customer/visitor, store data before save
     *
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->hasVisitorId()) {
            $this->setVisitorId($this->getVisitorId());
        }
        if (!$this->hasCustomerId()) {
            $this->setCustomerId($this->getCustomerId());
        }
        if (!$this->hasStoreId()) {
            $this->setStoreId($this->getStoreId());
        }
        if (!$this->hasAddedAt()) {
            $this->setAddedAt(now());
        }

        return $this;
    }

    /**
     * Retrieve visitor id
     *
     * if don't exists return current visitor id
     *
     * @return int
     */
    public function getVisitorId()
    {
        if ($this->hasData('visitor_id')) {
            return $this->getData('visitor_id');
        }
        return Mage::getSingleton('Mage_Log_Model_Visitor')->getId();
    }

    /**
     * Retrieve customer id
     *
     * if customer don't logged in return null
     *
     * @return int
     */
    public function getCustomerId()
    {
        if ($this->hasData('customer_id')) {
            return $this->getData('customer_id');
        }
        return Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId();
    }

    /**
     * Retrieve store id
     *
     * default return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->hasData('store_id')) {
            return $this->getData('store_id');
        }
        return Mage::app()->getStore()->getId();
    }

    /**
     * Retrieve resource instance wrapper
     *
     * @return Mage_Reports_Model_Resource_Product_Index_Abstract
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * On customer loggin merge visitor/customer index
     *
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    public function updateCustomerFromVisitor()
    {
        $this->_getResource()->updateCustomerFromVisitor($this);
        return $this;
    }

    /**
     * Purge visitor data by customer (logout)
     *
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    public function purgeVisitorByCustomer()
    {
        $this->_getResource()->purgeVisitorByCustomer($this);
        return $this;
    }

    /**
     * Retrieve Reports Session instance
     *
     * @return Mage_Reports_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_Reports_Model_Session');
    }

    /**
     * Calculate count of product index items cache
     *
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    public function calculate()
    {
        $collection = $this->getCollection()
            ->setCustomerId($this->getCustomerId())
            ->addIndexFilter()
            ->setVisibility(Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInSiteIds());

        $count = $collection->getSize();
        $this->_getSession()->setData($this->_countCacheKey, $count);
        return $this;
    }

    /**
     * Retrieve Exclude Product Ids List for Collection
     *
     * @return array
     */
    public function getExcludeProductIds()
    {
        return array();
    }

    /**
     * Retrieve count of product index items
     *
     * @return int
     */
    public function getCount()
    {
        if (!$this->_countCacheKey) {
            return 0;
        }

        if (!$this->_getSession()->hasData($this->_countCacheKey)) {
            $this->calculate();
        }

        return $this->_getSession()->getData($this->_countCacheKey);
    }

    /**
     * Clean index (visitors)
     *
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    public function clean()
    {
        $this->_getResource()->clean($this);
        return $this;
    }

    /**
     * Add product ids to current visitor/customer log
     * @param array $productIds
     * @return Mage_Reports_Model_Product_Index_Abstract
     */
    public function registerIds($productIds)
    {
        $this->_getResource()->registerIds($this, $productIds);
        $this->_getSession()->unsData($this->_countCacheKey);
        return $this;
    }
}
