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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Popular tags collection model
 *
 * @category    Mage
 * @package     Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Model_Resource_Popular_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Defines resource model and model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Tag_Model_Tag', 'Mage_Tag_Model_Resource_Tag');
    }

    /**
     * Replacing popularity by sum of popularity and base_popularity
     *
     * @param int $storeId
     * @return Mage_Tag_Model_Resource_Popular_Collection
     */
    public function joinFields($storeId = 0)
    {
        $this->getSelect()
            ->reset()
            ->from(
                array('tag_summary' => $this->getTable('tag_summary')),
                array('popularity' => 'tag_summary.popularity'))
            ->joinInner(
                array('tag' => $this->getTable('tag')),
                'tag.tag_id = tag_summary.tag_id AND tag.status = ' . Mage_Tag_Model_Tag::STATUS_APPROVED)
            ->where('tag_summary.store_id = ?', $storeId)
            ->where('tag_summary.products > ?', 0)
            ->order('popularity ' . Varien_Db_Select::SQL_DESC);

        return $this;
    }

    /**
     * Add filter by specified tag status
     *
     * @param string $statusCode
     * @return Mage_Tag_Model_Resource_Popular_Collection
     */
    public function addStatusFilter($statusCode)
    {
        $this->getSelect()->where('main_table.status = ?', $statusCode);
        return $this;
    }

    /**
     * Loads collection
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Mage_Tag_Model_Resource_Popular_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        parent::load($printQuery, $logQuery);
        return $this;
    }

    /**
     * Sets limit
     *
     * @param int $limit
     * @return Mage_Tag_Model_Resource_Popular_Collection
     */
    public function limit($limit)
    {
        $this->getSelect()->limit($limit);
        return $this;
    }

    /**
     * Get SQL for get record count
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();
        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $countSelect = $this->getConnection()->select();
        $countSelect->from(array('a' => $select), 'COUNT(popularity)');
        return $countSelect;
    }
}
