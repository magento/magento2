<?php
/**
 * Catalog Configurable Product Attribute Collection
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
{
    /**
     * Configurable attributes label table name
     *
     * @var string
     */
    protected $_labelTable;

    /**
     * Product instance
     *
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Catalog product type configurable
     *
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_productTypeConfigurable;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute $resource
     * @param \Zend_Db_Adapter_Abstract $connection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute $resource,
        $connection = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_catalogData = $catalogData;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Initialize connection and define table names
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute',
            'Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute'
        );
        $this->_labelTable = $this->getTable('catalog_product_super_attribute_label');
    }

    /**
     * Set Product filter (Configurable)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProductFilter($product)
    {
        $this->_product = $product;
        return $this->addFieldToFilter('product_id', $product->getId());
    }

    /**
     * Get product type
     *
     * @return \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private function getProductType()
    {
        return $this->_productTypeConfigurable;
    }

    /**
     * Set order collection by Position
     *
     * @param string $dir
     * @return $this
     */
    public function orderByPosition($dir = self::SORT_ORDER_ASC)
    {
        $this->setOrder('position ', $dir);
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
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        \Magento\Framework\Profiler::start('TTT1:' . __METHOD__, ['group' => 'TTT1', 'method' => __METHOD__]);
        $this->_addProductAttributes();
        \Magento\Framework\Profiler::stop('TTT1:' . __METHOD__);
        \Magento\Framework\Profiler::start('TTT2:' . __METHOD__, ['group' => 'TTT2', 'method' => __METHOD__]);
        $this->_addAssociatedProductFilters();
        \Magento\Framework\Profiler::stop('TTT2:' . __METHOD__);
        \Magento\Framework\Profiler::start('TTT3:' . __METHOD__, ['group' => 'TTT3', 'method' => __METHOD__]);
        $this->_loadLabels();
        \Magento\Framework\Profiler::stop('TTT3:' . __METHOD__);
        \Magento\Framework\Profiler::start('TTT4:' . __METHOD__, ['group' => 'TTT4', 'method' => __METHOD__]);
        $this->loadOptions();
        \Magento\Framework\Profiler::stop('TTT4:' . __METHOD__);
        return $this;
    }

    /**
     * Add product attributes to collection items
     *
     * @return $this
     */
    protected function _addProductAttributes()
    {
        foreach ($this->_items as $item) {
            $productAttribute = $this->getProductType()->getAttributeById(
                $item->getAttributeId(),
                $this->getProduct()
            );
            $item->setProductAttribute($productAttribute);
        }
        return $this;
    }

    /**
     * Add Associated Product Filters (From Product Type Instance)
     *
     * @return $this
     */
    public function _addAssociatedProductFilters()
    {
        $this->getProductType()->getUsedProducts(
            $this->getProduct(),
            $this->getColumnValues('attribute_id') // Filter associated products
        );
        return $this;
    }

    /**
     * Load attribute labels
     *
     * @return $this
     */
    protected function _loadLabels()
    {
        if ($this->count()) {
            $useDefaultCheck = $this->getConnection()->getCheckSql(
                'store.use_default IS NULL',
                'def.use_default',
                'store.use_default'
            );

            $labelCheck = $this->getConnection()->getCheckSql('store.value IS NULL', 'def.value', 'store.value');

            $select = $this->getConnection()->select()->from(
                ['def' => $this->_labelTable]
            )->joinLeft(
                ['store' => $this->_labelTable],
                $this->getConnection()->quoteInto(
                    'store.product_super_attribute_id = def.product_super_attribute_id AND store.store_id = ?',
                    $this->getStoreId()
                ),
                ['use_default' => $useDefaultCheck, 'label' => $labelCheck]
            )->where(
                'def.product_super_attribute_id IN (?)',
                array_keys($this->_items)
            )->where(
                'def.store_id = ?',
                0
            );

            $result = $this->getConnection()->fetchAll($select);
            foreach ($result as $data) {
                $this->getItemById($data['product_super_attribute_id'])->setLabel($data['label']);
                $this->getItemById($data['product_super_attribute_id'])->setUseDefault($data['use_default']);
            }
        }
        return $this;
    }

    /**
     * @return void
     */
    protected function loadOptions()
    {
        $usedProducts = $this->getProductType()->getUsedProducts($this->getProduct());
        if ($usedProducts) {
            foreach ($this->_items as $item) {
                $values = [];

                $productAttribute = $item->getProductAttribute();
                if (!$productAttribute instanceof AbstractAttribute) {
                    continue;
                }
                $itemId = $item->getId();
                $options = $this->getIncludedOptions($usedProducts, $productAttribute);
                foreach ($options as $option) {
                    foreach ($usedProducts as $associatedProduct) {
                        $attributeCodeValue = $associatedProduct->getData($productAttribute->getAttributeCode());
                        if (!empty($option['value']) && $option['value'] == $attributeCodeValue) {
                                $values[$itemId . ':' . $option['value']] = [
                                    'value_index' => $option['value'],
                                    'label' => $option['label'],
                                    'product_super_attribute_id' => $itemId,
                                    'default_label' => $option['label'],
                                    'store_label' => $option['label'],
                                    'use_default_value' => true,
                                ];
                        }
                    }
                }
                $values = array_values($values);
                $item->setOptions($values);
            }
        }
    }

    /**
     * Get options for all product attribute values from used products
     *
     * @param \Magento\Catalog\Model\Product[] $usedProducts
     * @param AbstractAttribute $productAttribute
     * @return array
     */
    protected function getIncludedOptions(array $usedProducts, AbstractAttribute $productAttribute)
    {
        $attributeValues = [];
        foreach ($usedProducts as $associatedProduct) {
            $attributeValues[] = $associatedProduct->getData($productAttribute->getAttributeCode());
        }
        $options = $productAttribute->getSource()->getSpecificOptions(array_unique($attributeValues));
        return $options;
    }

    /**
     * Retrieve product instance
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_product;
    }
}
