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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Products Most Viewed Report collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Product_Viewed_Collection extends Mage_Reports_Model_Resource_Product_Collection
{
    /**
     * List of store ids for the current collection will be filtered by
     *
     * @var array
     */
    protected $_storeIds = array();

    /**
     * Join fields
     *
     * @param string $from
     * @param string $to
     * @return Mage_Reports_Model_Resource_Product_Viewed_Collection
     */
    protected function _joinFields($from = '', $to = '')
    {
        $this->addAttributeToSelect('*')
            ->addViewsCount($from, $to);
        return $this;
    }

    /**
     * Set date range
     *
     * @param string $from
     * @param string $to
     * @return Mage_Reports_Model_Resource_Product_Viewed_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->_joinFields($from, $to);
        return $this;
    }

    /**
     * Set store ids
     *
     * @param array $storeIds
     * @return Mage_Reports_Model_Resource_Product_Viewed_Collection
     */
    public function setStoreIds($storeIds)
    {
        $storeId = array_pop($storeIds);
        $this->setStoreId($storeId);
        $this->addStoreFilter($storeId);
        $this->addStoreIds($storeId);
        return $this;
    }

    /**
     * Add store ids to filter 'report_event' data by store
     *
     * @param array|int $storeIds
     * @return Mage_Reports_Model_Resource_Product_Viewed_Collection
     */
    public function addStoreIds($storeIds)
    {
        if (is_array($storeIds)) {
            $this->_storeIds = array_merge($this->_storeIds, $storeIds);
        } else {
            $this->_storeIds[] = $storeIds;
        }
        return $this;
    }

    /**
     * Apply store filter
     *
     * @return Mage_Reports_Model_Resource_Product_Viewed_Collection
     */
    protected function _applyStoreIds()
    {
        $this->getSelect()->where('store_id IN(?)', $this->_storeIds);
        return $this;
    }

    /**
     * Apply filters
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function _beforeLoad()
    {
        $this->_applyStoreIds();
        return parent::_beforeLoad();
    }
}
