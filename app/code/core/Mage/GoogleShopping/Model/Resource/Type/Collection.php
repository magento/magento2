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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GoogleShopping Item Types collection
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Model_Resource_Type_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('Mage_GoogleShopping_Model_Type', 'Mage_GoogleShopping_Model_Resource_Type');
    }

    /**
     * Init collection select
     *
     * @return Mage_GoogleShopping_Model_Resource_Type_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->_joinAttributeSet();
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
       $paginatorAdapter = new Zend_Paginator_Adapter_DbSelect($this->getSelect());
       return $paginatorAdapter->getCountSelect();
   }

    /**
     * Add total count of Items for each type
     *
     * @return Mage_GoogleShopping_Model_Resource_Type_Collection
     */
    public function addItemsCount()
    {
        $this->getSelect()
            ->joinLeft(
                array('items'=>$this->getTable('googleshopping_items')),
                'main_table.type_id=items.type_id',
                array('items_total' => new Zend_Db_Expr('COUNT(items.item_id)')))
            ->group('main_table.type_id');
        return $this;
    }

    /**
     * Add country ISO filter to collection
     *
     * @param string $iso Two-letter country ISO code
     * @return Mage_GoogleShopping_Model_Resource_Type_Collection
     */
    public function addCountryFilter($iso)
    {
        $this->getSelect()->where('target_country=?', $iso);
        return $this;
    }

    /**
     * Join Attribute Set data
     *
     * @return Mage_GoogleShopping_Model_Resource_Type_Collection
     */
    protected function _joinAttributeSet()
    {
        $this->getSelect()
            ->join(
                array('set'=>$this->getTable('eav_attribute_set')),
                'main_table.attribute_set_id=set.attribute_set_id',
                array('attribute_set_name' => 'set.attribute_set_name'));
        return $this;
    }
}
