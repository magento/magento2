<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Pool;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Catalog\Model\Product\Type\Price\Factory as PriceFactory;
use Magento\Catalog\Model\Product\Type\Simple;
use Magento\Catalog\Model\ProductTypes\ConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Pricing\PriceInfo\Factory as PriceInfoFactory;

/**
 * Product type model
 *
 *
 * @api
 * @since 100.0.2
 */
class Type implements OptionSourceInterface, ResetAfterRequestInterface
{
    public const TYPE_SIMPLE = 'simple';

    public const TYPE_BUNDLE = 'bundle';

    public const TYPE_VIRTUAL = 'virtual';

    public const DEFAULT_TYPE = 'simple';

    public const DEFAULT_TYPE_MODEL = Simple::class;

    public const DEFAULT_PRICE_MODEL = Price::class;

    /**
     * @var ConfigInterface
     */
    protected $_config;

    /**
     * Product types
     *
     * @var array|string
     */
    protected $_types;

    /**
     * Composite product type Ids
     *
     * @var array
     */
    protected $_compositeTypes;

    /**
     * @var array|null|Price
     */
    protected $_priceModels;

    /**
     * Product types by type indexing priority
     *
     * @var array
     */
    protected $_typesPriority;

    /**
     * Product type factory
     *
     * @var Pool
     */
    protected $_productTypePool;

    /**
     * Price model factory
     *
     * @var PriceFactory
     */
    protected $_priceFactory;

    /**
     * @var PriceInfoFactory
     */
    protected $_priceInfoFactory;

    /**
     * Construct
     *
     * @param ConfigInterface $config
     * @param Pool $productTypePool
     * @param PriceFactory $priceFactory
     * @param PriceInfoFactory $priceInfoFactory
     */
    public function __construct(
        ConfigInterface $config,
        Pool $productTypePool,
        PriceFactory $priceFactory,
        PriceInfoFactory $priceInfoFactory
    ) {
        $this->_config = $config;
        $this->_productTypePool = $productTypePool;
        $this->_priceFactory = $priceFactory;
        $this->_priceInfoFactory = $priceInfoFactory;
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->_priceModels = null;
    }

    /**
     * Factory to product singleton product type instances
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return \Magento\Catalog\Model\Product\Type\AbstractType
     */
    public function factory($product)
    {
        $types = $this->getTypes();
        $typeId = $product->getTypeId();

        if (!empty($types[$typeId]['model'])) {
            $typeModelName = $types[$typeId]['model'];
        } else {
            $typeModelName = self::DEFAULT_TYPE_MODEL;
            $typeId = self::DEFAULT_TYPE;
        }

        $typeModel = $this->_productTypePool->get($typeModelName);
        $typeModel->setConfig($types[$typeId]);
        $typeModel->setTypeId($typeId);

        return $typeModel;
    }

    /**
     * Product type price model factory
     *
     * @param string $productType
     * @return Price
     */
    public function priceFactory($productType)
    {
        if (isset($this->_priceModels[$productType])) {
            return $this->_priceModels[$productType];
        }

        $types = $this->getTypes();

        if (!empty($types[$productType]['price_model'])) {
            $priceModelName = $types[$productType]['price_model'];
        } else {
            $priceModelName = self::DEFAULT_PRICE_MODEL;
        }

        $this->_priceModels[$productType] = $this->_priceFactory->create($priceModelName);
        return $this->_priceModels[$productType];
    }

    /**
     * Get Product Price Info object
     *
     * @param Product $saleableItem
     * @return \Magento\Framework\Pricing\PriceInfoInterface
     */
    public function getPriceInfo(Product $saleableItem)
    {
        return $this->_priceInfoFactory->create($saleableItem);
    }

    /**
     * Get product type labels array
     *
     * @return array
     */
    public function getOptionArray()
    {
        $options = [];
        foreach ($this->getTypes() as $typeId => $type) {
            $options[$typeId] = (string)$type['label'];
        }
        return $options;
    }

    /**
     * Get product type labels array with empty value
     *
     * @return array
     */
    public function getAllOption()
    {
        $options = $this->getOptionArray();
        array_unshift($options, ['value' => '', 'label' => '']);
        return $options;
    }

    /**
     * Get product type labels array with empty value for option element
     *
     * @return array
     */
    public function getAllOptions()
    {
        $res = $this->getOptions();
        array_unshift($res, ['value' => '', 'label' => '']);
        return $res;
    }

    /**
     * Get product type labels array for option element
     *
     * @return array
     */
    public function getOptions()
    {
        $res = [];
        foreach ($this->getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }
        return $res;
    }

    /**
     * Get product type label
     *
     * @param string $optionId
     * @return null|string
     */
    public function getOptionText($optionId)
    {
        $options = $this->getOptionArray();
        return $options[$optionId] ?? null;
    }

    /**
     * Get product types
     *
     * @return array
     */
    public function getTypes()
    {
        if ($this->_types === null) {
            $productTypes = $this->_config->getAll();
            foreach ($productTypes as $productTypeKey => $productTypeConfig) {
                $productTypes[$productTypeKey]['label'] = __($productTypeConfig['label']);
            }
            $this->_types = $productTypes;
        }
        return $this->_types;
    }

    /**
     * Return composite product type Ids
     *
     * @return array
     */
    public function getCompositeTypes()
    {
        if ($this->_compositeTypes === null) {
            $this->_compositeTypes = [];
            $types = $this->getTypes();
            foreach ($types as $typeId => $typeInfo) {
                if (array_key_exists('composite', $typeInfo) && $typeInfo['composite']) {
                    $this->_compositeTypes[] = $typeId;
                }
            }
        }
        return $this->_compositeTypes;
    }

    /**
     * Return product types by type indexing priority
     *
     * @return array
     */
    public function getTypesByPriority()
    {
        if ($this->_typesPriority === null) {
            $this->_typesPriority = [];
            $simplePriority = [];
            $compositePriority = [];

            $types = $this->getTypes();
            foreach ($types as $typeId => $typeInfo) {
                $priority = isset($typeInfo['index_priority']) ? abs((int) $typeInfo['index_priority']) : 0;
                if (!empty($typeInfo['composite'])) {
                    $compositePriority[$typeId] = $priority;
                } else {
                    $simplePriority[$typeId] = $priority;
                }
            }

            asort($simplePriority, SORT_NUMERIC);
            asort($compositePriority, SORT_NUMERIC);

            foreach (array_keys($simplePriority) as $typeId) {
                $this->_typesPriority[$typeId] = $types[$typeId];
            }
            foreach (array_keys($compositePriority) as $typeId) {
                $this->_typesPriority[$typeId] = $types[$typeId];
            }
        }
        return $this->_typesPriority;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return $this->getOptions();
    }
}
