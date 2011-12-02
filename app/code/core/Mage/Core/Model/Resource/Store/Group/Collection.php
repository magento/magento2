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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Store group collection
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Store_Group_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define resource model
     *
     */
    protected function _construct()
    {
        $this->setFlag('load_default_store_group', false);
        $this->_init('Mage_Core_Model_Store_Group', 'Mage_Core_Model_Resource_Store_Group');
    }

    /**
     * Set flag for load default (admin) store
     *
     * @param boolean $loadDefault
     * @return Mage_Core_Model_Resource_Store_Group_Collection
     */
    public function setLoadDefault($loadDefault)
    {
        $this->setFlag('load_default_store_group', (bool)$loadDefault);
        return $this;
    }

    /**
     * Is load default (admin) store
     *
     * @return boolean
     */
    public function getLoadDefault()
    {
        return $this->getFlag('load_default_store_group');
    }

    /**
     *  Add disable default store group filter to collection
     *
     * @return Mage_Core_Model_Resource_Store_Group_Collection
     */
    public function setWithoutDefaultFilter()
    {
        $this->addFieldToFilter('main_table.group_id', array('gt' => 0));
        return $this;
    }

    /**
     * Load collection data
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return Mage_Core_Model_Resource_Store_Group_Collection
     */
    public function _beforeLoad()
    {
        if (!$this->getLoadDefault()) {
            $this->setWithoutDefaultFilter();
        }
        $this->addOrder('main_table.name',  self::SORT_ORDER_ASC);
        return parent::_beforeLoad();
    }

    /**
     * Convert collection items to array for select options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('group_id', 'name');
    }

     /**
     * Add filter by website to collection
     *
     * @param int|array $website
     * @return Mage_Core_Model_Resource_Store_Collection
     */
    public function addWebsiteFilter($website)
    {
        return $this->addFieldToFilter('main_table.website_id', array('in' => $website));
    }

}
