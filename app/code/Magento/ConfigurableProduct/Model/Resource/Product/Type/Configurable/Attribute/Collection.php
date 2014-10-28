<?php
/**
 * Catalog Configurable Product Attribute Collection
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute\Price\Data as PriceData;

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
     * Configurable attributes price table name
     *
     * @var string
     */
    protected $_priceTable;

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
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Price values cache
     *
     * @var PriceData
     */
    protected $priceData;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute $resource
     * @param PriceData $priceData
     * @param \Zend_Db_Adapter_Abstract $connection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\EntityFactory $entityFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ConfigurableProduct\Model\Resource\Product\Type\Configurable\Attribute $resource,
        PriceData $priceData,
        $connection = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_catalogData = $catalogData;
        $this->priceData = $priceData;
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
        $this->_priceTable = $this->getTable('catalog_product_super_attribute_pricing');
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
        \Magento\Framework\Profiler::start('TTT1:' . __METHOD__, array('group' => 'TTT1', 'method' => __METHOD__));
        $this->_addProductAttributes();
        \Magento\Framework\Profiler::stop('TTT1:' . __METHOD__);
        \Magento\Framework\Profiler::start('TTT2:' . __METHOD__, array('group' => 'TTT2', 'method' => __METHOD__));
        $this->_addAssociatedProductFilters();
        \Magento\Framework\Profiler::stop('TTT2:' . __METHOD__);
        \Magento\Framework\Profiler::start('TTT3:' . __METHOD__, array('group' => 'TTT3', 'method' => __METHOD__));
        $this->_loadLabels();
        \Magento\Framework\Profiler::stop('TTT3:' . __METHOD__);
        \Magento\Framework\Profiler::start('TTT4:' . __METHOD__, array('group' => 'TTT4', 'method' => __METHOD__));
        $this->_loadPrices();
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
                array('def' => $this->_labelTable)
            )->joinLeft(
                array('store' => $this->_labelTable),
                $this->getConnection()->quoteInto(
                    'store.product_super_attribute_id = def.product_super_attribute_id AND store.store_id = ?',
                    $this->getStoreId()
                ),
                array('use_default' => $useDefaultCheck, 'label' => $labelCheck)
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
     * Load attribute prices information
     *
     * @return $this
     */
    protected function _loadPrices()
    {
        if ($this->count()) {

            $values = $this->getPriceValues();

            foreach ($values as $data) {
                $this->getItemById($data['product_super_attribute_id'])->addPrice($data);
            }
        }
        return $this;
    }

    /**
     * Retrieve price values
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getPriceValues()
    {
        $cachedPriceData = $this->priceData->getProductPrice($this->getProduct()->getId());
        if (false !== $cachedPriceData) {
            return $cachedPriceData;
        }

        $pricings = array(0 => array());

        if ($this->_catalogData->isPriceGlobal()) {
            $websiteId = 0;
        } else {
            $websiteId = (int) $this->_storeManager->getStore($this->getStoreId())->getWebsiteId();
            $pricing[$websiteId] = array();
        }

        $select = $this->getConnection()->select()->from(
            array('price' => $this->_priceTable)
        )->where(
            'price.product_super_attribute_id IN (?)',
            array_keys($this->_items)
        );

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
                if (!$productAttribute instanceof AbstractAttribute) {
                    continue;
                }
                $itemId = $item->getId();
                $options = $this->getIncludedOptions($usedProducts, $productAttribute);
                foreach ($options as $option) {
                    foreach ($usedProducts as $associatedProduct) {
                        $attributeCodeValue = $associatedProduct->getData($productAttribute->getAttributeCode());
                        if (!empty($option['value']) && $option['value'] == $attributeCodeValue) {
                            // If option available in associated product
                            if (!isset($values[$item->getId() . ':' . $option['value']])) {
                                $values[$itemId . ':' . $option['value']] = array(
                                    'product_super_attribute_id' => $itemId,
                                    'value_index' => $option['value'],
                                    'label' => $option['label'],
                                    'default_label' => $option['label'],
                                    'store_label' => $option['label'],
                                    'is_percent' => 0,
                                    'pricing_value' => null,
                                    'use_default_value' => true
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
                $values[$valueKey]['pricing_value'] = $pricing['pricing_value'];
                $values[$valueKey]['is_percent'] = $pricing['is_percent'];
                $values[$valueKey]['value_id'] = $pricing['value_id'];
                $values[$valueKey]['use_default_value'] = true;
            }
        }

        if ($websiteId && isset($pricings[$websiteId])) {
            foreach ($pricings[$websiteId] as $pricing) {
                $valueKey = $pricing['product_super_attribute_id'] . ':' . $pricing['value_index'];
                if (isset($values[$valueKey])) {
                    $values[$valueKey]['pricing_value'] = $pricing['pricing_value'];
                    $values[$valueKey]['is_percent'] = $pricing['is_percent'];
                    $values[$valueKey]['value_id'] = $pricing['value_id'];
                    $values[$valueKey]['use_default_value'] = false;
                }
            }
        }

        $this->priceData->setProductPrice($this->getProduct()->getId(), $values);

        return $values;
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
