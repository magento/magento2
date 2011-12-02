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
 * Report Products Tags collection
 *
 * @category    Mage
 * @package     Mage_Reports
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Reports_Model_Resource_Tag_Product_Collection extends Mage_Tag_Model_Resource_Product_Collection
{
    protected function _construct()
    {
        parent::_construct();
        /**
         * Allow to use analytic function
         */
        $this->_useAnalyticFunction = true;
    }
    /**
     * Add unique target count to result
     *
     * @return Mage_Reports_Model_Resource_Tag_Product_Collection
     */
    public function addUniqueTagedCount()
    {
        $select = clone $this->getSelect();
        
        $select->reset()
            ->from(array('rel' => $this->getTable('tag_relation')), 'COUNT(DISTINCT rel.tag_id)')
            ->where('rel.product_id = e.entity_id');

        $this->getSelect()
            ->columns(array('utaged' => new Zend_Db_Expr(sprintf('(%s)', $select))));
        return $this;
    }

    /**
     * Add all target count to result
     *
     * @return Mage_Reports_Model_Resource_Tag_Product_Collection
     */
    public function addAllTagedCount()
    {
        $this->getSelect()
            ->columns(array('taged' => 'COUNT(relation.tag_id)'));
        return $this;
    }

    /**
     * Add target count to result
     *
     * @return Mage_Reports_Model_Resource_Tag_Product_Collection
     */
    public function addTagedCount()
    {
        $this->getSelect()
            ->columns(array('taged' => 'COUNT(relation.tag_relation_id)'));

        return $this;
    }

    /**
     * Add group by product to result
     *
     * @return Mage_Reports_Model_Resource_Tag_Product_Collection
     */
    public function addGroupByProduct()
    {
        $this->getSelect()
            ->group('relation.product_id');
        return $this;
    }

    /**
     * Add group by tag to result
     *
     * @return Mage_Reports_Model_Resource_Tag_Product_Collection
     */
    public function addGroupByTag()
    {
        $this->getSelect()
            ->group('relation.tag_id');
        return $this;
    }

    /**
     * Add product filter
     *
     * @param int $customerId
     * @return Mage_Reports_Model_Resource_Tag_Product_Collection
     */
    public function addProductFilter($customerId)
    {
        $this->getSelect()
             ->where('relation.product_id = ?', (int)$customerId);
        $this->_customerFilterId = (int)$customerId;
        return $this;
    }

    /**
     * Set order
     *
     * @param string $attribute
     * @param string $dir
     * @return Mage_Reports_Model_Resource_Tag_Product_Collection
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if ($attribute == 'utaged' || $attribute == 'taged' || $attribute == 'tag_name') {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * Join fields
     *
     * @return Mage_Reports_Model_Resource_Tag_Product_Collection
     */
    protected function _joinFields()
    {
        $this->addAttributeToSelect('name');
        $this->getSelect()
            ->join(
                array('relation' => $this->getTable('tag_relation')),
                'relation.product_id = e.entity_id',
                array())
            ->join(
                array('t' => $this->getTable('tag')),
                't.tag_id = relation.tag_id',
                array('tag_id',  'status', 'tag_name' => 'name')
            );

        return $this;
    }
}
