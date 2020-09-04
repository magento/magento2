<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Pricing\Price\BasePrice;
use Magento\Catalog\Pricing\Price\CustomOptionPriceCalculator;
use Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Catalog product option select type model
 *
 * @api
 * @method int getOptionId()
 * @method \Magento\Catalog\Model\Product\Option\Value setOptionId(int $value)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) - added use of constants instead of string literals:
 *      BasePrice::PRICE_CODE - instead of 'base_price'
 *      RegularPrice::PRICE_CODE - instead of 'regular_price'
 * @since 100.0.2
 */
class Value extends AbstractModel implements \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface
{
    /**
     * Option type percent
     */
    const TYPE_PERCENT = 'percent';

    /**#@+
     * Constants
     */
    const KEY_TITLE = 'title';
    const KEY_SORT_ORDER = 'sort_order';
    const KEY_PRICE = 'price';
    const KEY_PRICE_TYPE = 'price_type';
    const KEY_SKU = 'sku';
    const KEY_OPTION_TYPE_ID = 'option_type_id';
    /**#@-*/

    /**#@-*/
    protected $_values = [];

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var Option
     */
    protected $_option;

    /**
     * Value collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory
     */
    protected $_valueCollectionFactory;

    /**
     * @var CustomOptionPriceCalculator
     */
    private $customOptionPriceCalculator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory $valueCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param CustomOptionPriceCalculator|null $customOptionPriceCalculator
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory $valueCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        CustomOptionPriceCalculator $customOptionPriceCalculator = null
    ) {
        $this->_valueCollectionFactory = $valueCollectionFactory;
        $this->customOptionPriceCalculator = $customOptionPriceCalculator
            ?? \Magento\Framework\App\ObjectManager::getInstance()->get(CustomOptionPriceCalculator::class);

        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Override parent _construct method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Product\Option\Value::class);
    }

    /**
     * Add value to values array
     *
     * @codeCoverageIgnoreStart
     * @param mixed $value
     * @return $this
     */
    public function addValue($value)
    {
        $this->_values[] = $value;
        return $this;
    }

    /**
     * Returns array of values
     *
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * Set values array
     *
     * @param array $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->_values = $values;
        return $this;
    }

    /**
     * Unset all from values array
     *
     * @return $this
     */
    public function unsetValues()
    {
        $this->_values = [];
        return $this;
    }

    /**
     * Set option
     *
     * @param Option $option
     * @return $this
     */
    public function setOption(Option $option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * Unset option
     *
     * @return $this
     */
    public function unsetOption()
    {
        $this->_option = null;
        return $this;
    }

    /**
     * Enter description here...
     *
     * @return Option
     */
    public function getOption()
    {
        return $this->_option;
    }

    /**
     * Set product
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    //@codeCoverageIgnoreEnd

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        if ($this->_product === null) {
            $this->_product = $this->getOption()->getProduct();
        }
        return $this->_product;
    }

    /**
     * Save array of values
     *
     * @return $this
     */
    public function saveValues()
    {
        $option = $this->getOption();

        foreach ($this->getValues() as $value) {
            $this->isDeleted(false);
            $this->setData($value)
                ->setData('option_id', $option->getId())
                ->setData('store_id', $option->getStoreId());

            if ((bool) $this->getData('is_delete') === true) {
                if ($this->getId()) {
                    $this->deleteValues($this->getId());
                    $this->delete();
                }
            } else {
                $this->save();
            }
        }

        return $this;
    }

    /**
     * Return price. If $flag is true and price is percent return converted percent to price
     *
     * @param bool $flag
     * @return float|int
     */
    public function getPrice($flag = false)
    {
        if ($flag) {
            return $this->customOptionPriceCalculator->getOptionPriceByPriceCode($this, BasePrice::PRICE_CODE);
        }
        return $this->_getData(self::KEY_PRICE);
    }

    /**
     * Return regular price.
     *
     * @return float|int
     */
    public function getRegularPrice()
    {
        return $this->customOptionPriceCalculator->getOptionPriceByPriceCode($this, RegularPrice::PRICE_CODE);
    }

    /**
     * Enter description here...
     *
     * @param Option $option
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     */
    public function getValuesCollection(Option $option)
    {
        $collection = $this->_valueCollectionFactory->create()->addFieldToFilter(
            'option_id',
            $option->getId()
        )->getValues(
            $option->getStoreId()
        );

        return $collection;
    }

    /**
     * Returns values by option
     *
     * @param array $optionIds
     * @param int $option_id
     * @param int $store_id
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     */
    public function getValuesByOption($optionIds, $option_id, $store_id)
    {
        $collection = $this->_valueCollectionFactory->create()->addFieldToFilter(
            'option_id',
            $option_id
        )->getValuesByOption(
            $optionIds,
            $store_id
        );

        return $collection;
    }

    /**
     * Delete value by option
     *
     * @param int $option_id
     * @return $this
     */
    public function deleteValue($option_id)
    {
        $this->getResource()->deleteValue($option_id);
        return $this;
    }

    /**
     * Delete values by option
     *
     * @param int $option_type_id
     * @return $this
     */
    public function deleteValues($option_type_id)
    {
        $this->getResource()->deleteValues($option_type_id);
        return $this;
    }

    /**
     * Duplicate product options value
     *
     * @param int $oldOptionId
     * @param int $newOptionId
     * @return $this
     */
    public function duplicate($oldOptionId, $newOptionId)
    {
        $this->getResource()->duplicate($this, $oldOptionId, $newOptionId);
        return $this;
    }

    /**
     * Get option title
     *
     * @return string
     * @codeCoverageIgnoreStart
     */
    public function getTitle()
    {
        return $this->_getData(self::KEY_TITLE);
    }

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->_getData(self::KEY_SORT_ORDER);
    }

    /**
     * Get price type
     *
     * @return string
     */
    public function getPriceType()
    {
        return $this->_getData(self::KEY_PRICE_TYPE);
    }

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->_getData(self::KEY_SKU);
    }

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getOptionTypeId()
    {
        return $this->_getData(self::KEY_OPTION_TYPE_ID);
    }

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::KEY_SORT_ORDER, $sortOrder);
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        return $this->setData(self::KEY_PRICE, $price);
    }

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     */
    public function setPriceType($priceType)
    {
        return $this->setData(self::KEY_PRICE_TYPE, $priceType);
    }

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * Set Option type id
     *
     * @param int $optionTypeId
     * @return int|null
     */
    public function setOptionTypeId($optionTypeId)
    {
        return $this->setData(self::KEY_OPTION_TYPE_ID, $optionTypeId);
    }

    //@codeCoverageIgnoreEnd
}
