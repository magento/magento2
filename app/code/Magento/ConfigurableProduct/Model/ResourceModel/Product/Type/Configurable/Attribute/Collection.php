<?php
/**
 * Catalog Configurable Product Attribute Collection
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as ConfigurableResource;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /** @var ConfigurableResource */
    private $configurableResource;

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
     * @var Configurable
     */
    protected $_productTypeConfigurable;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Configurable $catalogProductTypeConfigurable
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Attribute $resource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Helper\Data $catalogData,
        Attribute $resource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
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
            'Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute'
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
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $this->_product = $product;
        return $this->addFieldToFilter('product_id', $product->getData($metadata->getLinkField()));
    }

    /**
     * Get product type
     *
     * @return Configurable
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
     * @deprecated
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
                $item = $this->getItemById($data['product_super_attribute_id']);
                $item->setLabel($data['label']);
                $item->setUseDefault($data['use_default']);
            }
        }
        return $this;
    }

    /**
     * @return void
     */
    protected function loadOptions()
    {
        /** @var ConfigurableResource $configurableResource */
        $configurableResource = $this->getConfigurableResource();
        foreach ($this->_items as $item) {
            $values = [];

            $productAttribute = $item->getProductAttribute();

            $itemId = $item->getId();
            $options = $configurableResource->getAttributeOptions(
                $productAttribute,
                $this->getProduct()->getData(
                    $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField()
                )
            );
            foreach ($options as $option) {
                $values[$itemId . ':' . $option['value_index']] = [
                    'value_index' => $option['value_index'],
                    'label' => $option['option_title'],
                    'product_super_attribute_id' => $itemId,
                    'default_label' => $option['default_title'],
                    'store_label' => $option['default_title'],
                    'use_default_value' => true,
                ];
            }
            $values = array_values($values);
            $item->setOptions($values);
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
    private function getProduct()
    {
        return $this->_product;
    }

    /**
     * @inheritdoc
     */
    public function __sleep()
    {
        return array_diff(
            parent::__sleep(),
            [
                '_product',
                '_catalogData',
                '_productTypeConfigurable',
                '_storeManager',
                'metadataPool',
                'configurableResource',
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = ObjectManager::getInstance();
        $this->_storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->_productTypeConfigurable = $objectManager->get(Configurable::class);
        $this->_catalogData = $objectManager->get(\Magento\Catalog\Helper\Data::class);
        $this->metadataPool = $objectManager->get(MetadataPool::class);
        $this->configurableResource = $objectManager->get(ConfigurableResource::class);
    }

    /**
     * Get MetadataPool instance
     * @return MetadataPool
     */
    private function getMetadataPool()
    {
        if (!$this->metadataPool) {
            $this->metadataPool = ObjectManager::getInstance()->get(MetadataPool::class);
        }
        return $this->metadataPool;
    }

    /**
     * Get Configurable Resource
     *
     * @return ConfigurableResource
     * @deprecated
     */
    private function getConfigurableResource()
    {
        if (!($this->configurableResource instanceof ConfigurableResource)) {
            $this->configurableResource = ObjectManager::getInstance()->get(
                ConfigurableResource::class
            );
        }
        return $this->configurableResource;
    }
}
