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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Configurable Product Attribute Collection
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Configurable attributes label table name
     *
     * @var string
     */
    protected $_labelTable;

    /**
     * Configurable attributes price table name
     *
     * @var string
     */
    protected $_priceTable;

    /**
     * Product instance
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product;

    /**
     * Initialize connection and define table names
     *
     */
    protected function _construct()
    {
        $this->_init(
            'Mage_Catalog_Model_Product_Type_Configurable_Attribute',
            'Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute'
        );
        $this->_labelTable = $this->getTable('catalog_product_super_attribute_label');
        $this->_priceTable = $this->getTable('catalog_product_super_attribute_pricing');
    }

    /**
     * Retrieve catalog helper
     *
     * @return Mage_Catalog_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('Mage_Catalog_Helper_Data');
    }

    /**
     * Set Product filter (Configurable)
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    public function setProductFilter($product)
    {
        $this->_product = $product;
        return $this->addFieldToFilter('product_id', $product->getId());
    }

    /**
     * Get product type
     *
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    private function getProductType()
    {
        return Mage::getSingleton('Mage_Catalog_Model_Product_Type_Configurable');
    }

    /**
     * Set order collection by Position
     *
     * @param string $dir
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    public function orderByPosition($dir = self::SORT_ORDER_ASC)
    {
        $this->setOrder('position ',  $dir);
        return $this;
    }

    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return (int)$this->_product->getStoreId();
    }

    /**
     * After load collection process
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        Magento_Profiler::start('TTT1:'.__METHOD__);
        $this->_addProductAttributes();
        Magento_Profiler::stop('TTT1:'.__METHOD__);
        Magento_Profiler::start('TTT2:'.__METHOD__);
        $this->_addAssociatedProductFilters();
        Magento_Profiler::stop('TTT2:'.__METHOD__);
        Magento_Profiler::start('TTT3:'.__METHOD__);
        $this->_loadLabels();
        Magento_Profiler::stop('TTT3:'.__METHOD__);
        Magento_Profiler::start('TTT4:'.__METHOD__);
        $this->_loadPrices();
        Magento_Profiler::stop('TTT4:'.__METHOD__);
        return $this;
    }

    /**
     * Add product attributes to collection items
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    protected function _addProductAttributes()
    {
        foreach ($this->_items as $item) {
            $productAttribute = $this->getProductType()
                ->getAttributeById($item->getAttributeId(), $this->getProduct());
            $item->setProductAttribute($productAttribute);
        }
        return $this;
    }

    /**
     * Add Associated Product Filters (From Product Type Instance)
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    public function _addAssociatedProductFilters()
    {
        $this->getProductType()
            ->getUsedProducts($this->getProduct(), $this->getColumnValues('attribute_id')); // Filter associated products
        return $this;
    }

    /**
     * Load attribute labels
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    protected function _loadLabels()
    {
        if ($this->count()) {
            $useDefaultCheck = $this->getConnection()->getCheckSql(
                'store.use_default IS NULL',
                'def.use_default',
                'store.use_default'
            );

            $labelCheck = $this->getConnection()->getCheckSql(
                'store.value IS NULL',
                'def.value',
                'store.value'
            );

            $select = $this->getConnection()->select()
                ->from(array('def' => $this->_labelTable))
                ->joinLeft(
                    array('store' => $this->_labelTable),
                    $this->getConnection()->quoteInto('store.product_super_attribute_id = def.product_super_attribute_id AND store.store_id = ?', $this->getStoreId()),
                    array(
                        'use_default' => $useDefaultCheck,
                        'label' => $labelCheck
                    ))
                ->where('def.product_super_attribute_id IN (?)', array_keys($this->_items))
                ->where('def.store_id = ?', 0);

                $result = $this->getConnection()->fetchAll($select);
                foreach ($result as $data) {
                    $this->getItemById($data['product_super_attribute_id'])->setLabel($data['label']);
                    $this->getItemById($data['product_super_attribute_id'])->setUseDefault($data['use_default']);
                }
        }
        return $this;
    }

    /**
     * Load attribute prices information
     *
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute_Collection
     */
    protected function _loadPrices()
    {
        if ($this->count()) {
            $pricings = array(
                0 => array()
            );

            if ($this->getHelper()->isPriceGlobal()) {
                $websiteId = 0;
            } else {
                $websiteId = (int)Mage::app()->getStore($this->getStoreId())->getWebsiteId();
                $pricing[$websiteId] = array();
            }

            $select = $this->getConnection()->select()
                ->from(array('price' => $this->_priceTable))
                ->where('price.product_super_attribute_id IN (?)', array_keys($this->_items));

            if ($websiteId > 0) {
                $select->where('price.website_id IN(?)', array(0, $websiteId));
            } else {
                $select->where('price.website_id = ?', 0);
            }

            $query = $this->getConnection()->query($select);

            while ($row = $query->fetch()) {
                $pricings[(int)$row['website_id']][] = $row;
            }

            $values = array();
            $usedProducts = $this->getProductType()->getUsedProducts($this->getProduct());
            if ($usedProducts) {
                foreach ($this->_items as $item) {
                    $productAttribute = $item->getProductAttribute();
                    if (!($productAttribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract)) {
                        continue;
                    }
                    $itemId = $item->getId();
                    $options = $productAttribute->getFrontend()->getSelectOptions();
                    foreach ($options as $option) {
                        foreach ($usedProducts as $associatedProduct) {
                            $attributeCodeValue = $associatedProduct->getData($productAttribute->getAttributeCode());
                            if (!empty($option['value']) && $option['value'] == $attributeCodeValue) {
                                // If option available in associated product
                                if (!isset($values[$item->getId() . ':' . $option['value']])) {
                                    $values[$itemId . ':' . $option['value']] = array(
                                        'product_super_attribute_id' => $itemId,
                                        'value_index'                => $option['value'],
                                        'label'                      => $option['label'],
                                        'default_label'              => $option['label'],
                                        'store_label'                => $option['label'],
                                        'is_percent'                 => 0,
                                        'pricing_value'              => null,
                                        'use_default_value'          => true
                                    );
                                }
                            }
                        }
                    }
                }
            }

            foreach ($pricings[0] as $pricing) {
                // Addding pricing to options
                $valueKey = $pricing['product_super_attribute_id'] . ':' . $pricing['value_index'];
                if (isset($values[$valueKey])) {
                    $values[$valueKey]['pricing_value']     = $pricing['pricing_value'];
                    $values[$valueKey]['is_percent']        = $pricing['is_percent'];
                    $values[$valueKey]['value_id']          = $pricing['value_id'];
                    $values[$valueKey]['use_default_value'] = true;
                }
            }

            if ($websiteId && isset($pricings[$websiteId])) {
                foreach ($pricings[$websiteId] as $pricing) {
                    $valueKey = $pricing['product_super_attribute_id'] . ':' . $pricing['value_index'];
                    if (isset($values[$valueKey])) {
                        $values[$valueKey]['pricing_value']     = $pricing['pricing_value'];
                        $values[$valueKey]['is_percent']        = $pricing['is_percent'];
                        $values[$valueKey]['value_id']          = $pricing['value_id'];
                        $values[$valueKey]['use_default_value'] = false;
                    }
                }
            }

            foreach ($values as $data) {
                $this->getItemById($data['product_super_attribute_id'])->addPrice($data);
            }
        }
        return $this;
    }

    /**
     * Retrive product instance
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_product;
    }
}
