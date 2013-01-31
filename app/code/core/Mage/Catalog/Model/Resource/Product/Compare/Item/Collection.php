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
 * Catalog Product Compare Items Resource Collection
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
    extends Mage_Catalog_Model_Resource_Product_Collection
{
    /**
     * Customer Filter
     *
     * @var int
     */
    protected $_customerId               = 0;

    /**
     * Visitor Filter
     *
     * @var int
     */
    protected $_visitorId                = 0;

    /**
     * Comparable attributes cache
     *
     * @var array
     */
    protected $_comparableAttributes;

    /**
     * Initialize resources
     */
    protected function _construct()
    {
        $this->_init('Mage_Catalog_Model_Product_Compare_Item', 'Mage_Catalog_Model_Resource_Product');
        $this->_initTables();
    }

    /**
     * Set customer filter to collection
     *
     * @param int $customerId
     * @return Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    public function setCustomerId($customerId)
    {
        $this->_customerId = (int)$customerId;
        $this->_addJoinToSelect();
        return $this;
    }

    /**
     * Set visitor filter to collection
     *
     * @param int $visitorId
     * @return Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    public function setVisitorId($visitorId)
    {
        $this->_visitorId = (int)$visitorId;
        $this->_addJoinToSelect();
        return $this;
    }

    /**
     * Retrieve customer filter applied to collection
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerId;
    }

    /**
     * Retrieve visitor filter applied to collection
     *
     * @return int
     */
    public function getVisitorId()
    {
        return $this->_visitorId;
    }

    /**
     * Retrieve condition for join filters
     *
     * @return array
     */
    public function getConditionForJoin()
    {
        if ($this->getCustomerId()) {
            return array('customer_id' => $this->getCustomerId());
        }

        if ($this->getVisitorId()) {
            return array('visitor_id' => $this->getVisitorId());
        }

        return array('customer_id' => array('null' => true),'visitor_id' => '0');
    }

    /**
     * Add join to select
     *
     * @return Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    public function _addJoinToSelect()
    {
        $this->joinTable(
            array('t_compare' => 'catalog_compare_item'),
            'product_id=entity_id',
            array(
                'product_id'    => 'product_id',
                'customer_id'   => 'customer_id',
                'visitor_id'    => 'visitor_id',
                'item_store_id' => 'store_id',
                'catalog_compare_item_id' => 'catalog_compare_item_id'
            ),
            $this->getConditionForJoin()
        );

        $this->_productLimitationFilters['store_table']  = 't_compare';

        return $this;
    }

    /**
     * Retrieve comapre products attribute set ids
     *
     * @return array
     */
    protected function _getAttributeSetIds()
    {
        // prepare compare items table conditions
        $compareConds = array(
            'compare.product_id=entity.entity_id',
        );
        if ($this->getCustomerId()) {
            $compareConds[] = $this->getConnection()
                ->quoteInto('compare.customer_id = ?', $this->getCustomerId());
        } else {
            $compareConds[] = $this->getConnection()
                ->quoteInto('compare.visitor_id = ?', $this->getVisitorId());
        }

        // prepare website filter
        $websiteId    = (int)Mage::app()->getStore($this->getStoreId())->getWebsiteId();
        $websiteConds = array(
            'website.product_id = entity.entity_id',
            $this->getConnection()->quoteInto('website.website_id = ?', $websiteId)
        );

        // retrieve attribute sets
        $select = $this->getConnection()->select()
            ->distinct(true)
            ->from(
                array('entity' => $this->getEntity()->getEntityTable()),
                'attribute_set_id')
            ->join(
                array('website' => $this->getTable('catalog_product_website')),
                join(' AND ', $websiteConds),
                array())
            ->join(
                array('compare' => $this->getTable('catalog_compare_item')),
                join(' AND ', $compareConds),
                array()
            );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Retrieve attribute ids by set ids
     *
     * @param array $setIds
     * @return array
     */
    protected function _getAttributeIdsBySetIds(array $setIds)
    {
        $select = $this->getConnection()->select()
            ->distinct(true)
            ->from($this->getTable('eav_entity_attribute'), 'attribute_id')
            ->where('attribute_set_id IN(?)', $setIds);
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Retrieve Merged comparable attributes for compared product items
     *
     * @return array
     */
    public function getComparableAttributes()
    {
        if ($this->_comparableAttributes === null) {
            $this->_comparableAttributes = array();
            $setIds = $this->_getAttributeSetIds();
            if ($setIds) {
                $attributeIds = $this->_getAttributeIdsBySetIds($setIds);

                $select = $this->getConnection()->select()
                    ->from(array('main_table' => $this->getTable('eav_attribute')))
                    ->join(
                        array('additional_table' => $this->getTable('catalog_eav_attribute')),
                        'additional_table.attribute_id=main_table.attribute_id'
                    )
                    ->joinLeft(
                        array('al' => $this->getTable('eav_attribute_label')),
                        'al.attribute_id = main_table.attribute_id AND al.store_id = ' . (int) $this->getStoreId(),
                        array('store_label' => $this->getConnection()->getCheckSql('al.value IS NULL', 'main_table.frontend_label', 'al.value'))
                    )
                    ->where('additional_table.is_comparable=?', 1)
                    ->where('main_table.attribute_id IN(?)', $attributeIds);
                $attributesData = $this->getConnection()->fetchAll($select);
                if ($attributesData) {
                    $entityType = Mage_Catalog_Model_Product::ENTITY;
                    Mage::getSingleton('Mage_Eav_Model_Config')
                        ->importAttributesData($entityType, $attributesData);
                    foreach ($attributesData as $data) {
                        $attribute = Mage::getSingleton('Mage_Eav_Model_Config')
                            ->getAttribute($entityType, $data['attribute_code']);
                        $this->_comparableAttributes[$attribute->getAttributeCode()] = $attribute;
                    }
                    unset($attributesData);
                }
            }
        }
        return $this->_comparableAttributes;
    }

    /**
     * Load Comparable attributes
     *
     * @return Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    public function loadComparableAttributes()
    {
        $comparableAttributes = $this->getComparableAttributes();
        $attributes = array();
        foreach ($comparableAttributes as $attribute) {
            $attributes[] = $attribute->getAttributeCode();
        }
        $this->addAttributeToSelect($attributes);

        return $this;
    }

    /**
     * Use product as collection item
     *
     * @return Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    public function useProductItem()
    {
        $this->setObject('Mage_Catalog_Model_Product');

        $this->setFlag('url_data_object', true);
        $this->setFlag('do_not_use_category_id', true);

        return $this;
    }

    /**
     * Retrieve product ids from collection
     *
     * @return array
     */
    public function getProductIds()
    {
        $ids = array();
        foreach ($this->getItems() as $item) {
            $ids[] = $item->getProductId();
        }

        return $ids;
    }

    /**
     * Clear compare items by condition
     *
     * @return Mage_Catalog_Model_Resource_Product_Compare_Item_Collection
     */
    public function clear()
    {
        Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product_Compare_Item')
            ->clearItems($this->getVisitorId(), $this->getCustomerId());
        Mage::dispatchEvent('catalog_product_compare_item_collection_clear');

        return $this;
    }

    /**
     * Retrieve is flat enabled flag
     * Overwrite disable flat for compared item if required EAV resource
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        if (!Mage::helper('Mage_Catalog_Helper_Product_Compare')->getAllowUsedFlat()) {
            return false;
        }
        return parent::isEnabledFlat();
    }
}
