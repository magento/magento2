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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Tags customer collection
 *
 * @category    Mage
 * @package     Mage_Tag
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Model_Resource_Customer_Collection extends Mage_Customer_Model_Resource_Customer_Collection
{
    /**
     * Allows disabling grouping
     *
     * @var bool
     */
    protected $_allowDisableGrouping     = true;

    /**
     * Count attribute for count sql
     *
     * @var string
     */
    protected $_countAttribute           = 'tr.tag_id';

    /**
     * Array with joined tables
     *
     * @var array
     */
    protected $_joinFlags                = array();

    /**
     * Prepare select
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $this->_joinFields();
        $this->_setIdFieldName('tag_relation_id');
        return $this;
    }

    /**
     * Adds filter by tag is
     *
     * @param int $tagId
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addTagFilter($tagId)
    {
        $this->getSelect()
            ->where('tr.tag_id = ?', $tagId);
        return $this;
    }

    /**
     * adds filter by product id
     *
     * @param int $productId
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addProductFilter($productId)
    {
        $this->getSelect()
            ->where('tr.product_id = ?', $productId);
        return $this;
    }

    /**
     * Apply filter by store id(s).
     *
     * @param int|array $storeId
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addStoreFilter($storeId)
    {
        $this->getSelect()->where('tr.store_id IN (?)', $storeId);
        return $this;
    }

    /**
     * Adds filter by status
     *
     * @param int $status
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addStatusFilter($status)
    {
        $this->getSelect()
            ->where('t.status = ?', $status);
        return $this;
    }

    /**
     * Adds desc order by tag relation id
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addDescOrder()
    {
        $this->getSelect()
            ->order('tr.tag_relation_id desc');
        return $this;
    }

    /**
     * Adds grouping by tag id
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addGroupByTag()
    {
        $this->getSelect()
            ->group('tr.tag_id');

        /*
         * Allow analytic functions usage
         */
        $this->_useAnalyticFunction = true;

        $this->_allowDisableGrouping = true;
        return $this;
    }

    /**
     * Adds grouping by customer id
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addGroupByCustomer()
    {
        $this->getSelect()
            ->group('tr.customer_id');

        $this->_allowDisableGrouping = false;
        return $this;
    }

    /**
     * Disables grouping
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addGroupByCustomerProduct()
    {
        // Nothing need to group
        $this->_allowDisableGrouping = false;
        return $this;
    }

    /**
     * Adds filter by customer id
     *
     * @param int $customerId
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addCustomerFilter($customerId)
    {
        $this->getSelect()->where('tr.customer_id = ?', $customerId);
        return $this;
    }

    /**
     * Joins tables to select
     *
     */
    protected function _joinFields()
    {
        $tagRelationTable = $this->getTable('tag_relation');
        $tagTable = $this->getTable('tag');

        //TODO: add full name logic
        $this->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->addAttributeToSelect('email');

        $this->getSelect()
        ->join(
            array('tr' => $tagRelationTable),
            'tr.customer_id = e.entity_id',
            array('tag_relation_id', 'product_id', 'active', 'added_in' => 'store_id')
        )
        ->join(array('t' => $tagTable), 't.tag_id = tr.tag_id', array('*'));
    }

    /**
     * Gets number of rows
     *
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();

        if ($this->_allowDisableGrouping) {
            $countSelect->reset(Zend_Db_Select::COLUMNS);
            $countSelect->reset(Zend_Db_Select::GROUP);
            $countSelect->columns('COUNT(DISTINCT ' . $this->getCountAttribute() . ')');
        }
        return $countSelect;
    }

    /**
     * Adds Product names to item
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addProductName()
    {
        $productsId   = array();
        $productsData = array();

        foreach ($this->getItems() as $item) {
            $productsId[] = $item->getProductId();
        }

        $productsId = array_unique($productsId);

        /* small fix */
        if ( sizeof($productsId) == 0 ) {
            return;
        }

        $collection = Mage::getModel('Mage_Catalog_Model_Product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addIdFilter($productsId);

        $collection->load();

        foreach ($collection->getItems() as $item) {
            $productsData[$item->getId()] = $item->getName();
            $productsSku[$item->getId()] = $item->getSku();
        }

        foreach ($this->getItems() as $item) {
            $item->setProduct($productsData[$item->getProductId()]);
            $item->setProductSku($productsSku[$item->getProductId()]);
        }
        return $this;
    }

    /**
     * Adds Product names to select
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addProductToSelect()
    {
        $resource = Mage::getModel('Mage_Catalog_Model_Product')->getResource();

        // add product attributes to select
        foreach (array('name' => 'value') as $field => $fieldName) {
            $attr = $resource->getAttribute($field);
            $this->_select->joinLeft(
                array($field => $attr->getBackend()->getTable()),
                'tr.product_id = ' . $field . '.entity_id AND ' . $field . '.attribute_id = ' . $attr->getId(),
                array('product_' . $field => $fieldName)
            );
        }

        // add product fields
        $this->_select->joinLeft(
            array('p' => $this->getTable('catalog_product_entity')),
            'tr.product_id = p.entity_id',
            array('product_sku' => 'sku')
        );

        return $this;
    }

    /**
     * Sets attribute for count
     *
     * @param string $value
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function setCountAttribute($value)
    {
        $this->_countAttribute = $value;
        return $this;
    }

    /**
     * Gets attribure for count
     *
     * @return string
     */
    public function getCountAttribute()
    {
        return $this->_countAttribute;
    }

    /**
     * Adds field to filter
     *
     * @param string $attribute
     * @param array $condition
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        if ($attribute == 'name') {
            $where = $this->_getConditionSql('t.name', $condition);
            $this->getSelect()->where($where, null, Varien_Db_Select::TYPE_CONDITION);
            return $this;
        } else {
            return parent::addFieldToFilter($attribute, $condition);
        }
    }

    /**
     * Treat "order by" items as attributes to sort
     *
     * @return Mage_Tag_Model_Resource_Customer_Collection
     */
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            parent::_renderOrders();

            $orders = $this->getSelect()
                ->getPart(Zend_Db_Select::ORDER);

            $appliedOrders = array();
            foreach ($orders as $order) {
                $appliedOrders[$order[0]] = true;
            }

            foreach ($this->_orders as $field => $direction) {
                if (empty($appliedOrders[$field])) {
                    $this->_select->order(new Zend_Db_Expr($field . ' ' . $direction));
                }
            }
        }
        return $this;
    }
}
