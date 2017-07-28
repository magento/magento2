<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;
use Magento\Framework\Model\AbstractModel;

/**
 * Catalog product option select type model
 *
 * @api
 * @method \Magento\Catalog\Model\ResourceModel\Product\Option\Value _getResource()
 * @method \Magento\Catalog\Model\ResourceModel\Product\Option\Value getResource()
 * @method int getOptionId()
 * @method \Magento\Catalog\Model\Product\Option\Value setOptionId(int $value)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 * @since 2.0.0
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

    /**
     * @var array
     */
    protected $_values = [];

    /**
     * @var Product
     * @since 2.0.0
     */
    protected $_product;

    /**
     * @var Option
     * @since 2.0.0
     */
    protected $_option;

    /**
     * Value collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory
     * @since 2.0.0
     */
    protected $_valueCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory $valueCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ResourceModel\Product\Option\Value\CollectionFactory $valueCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_valueCollectionFactory = $valueCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Catalog\Model\ResourceModel\Product\Option\Value::class);
    }

    /**
     * @codeCoverageIgnoreStart
     * @param mixed $value
     * @return $this
     * @since 2.0.0
     */
    public function addValue($value)
    {
        $this->_values[] = $value;
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * @param array $values
     * @return $this
     * @since 2.0.0
     */
    public function setValues($values)
    {
        $this->_values = $values;
        return $this;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function unsetValues()
    {
        $this->_values = [];
        return $this;
    }

    /**
     * @param Option $option
     * @return $this
     * @since 2.0.0
     */
    public function setOption(Option $option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getOption()
    {
        return $this->_option;
    }

    /**
     * @param Product $product
     * @return $this
     * @since 2.0.0
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    //@codeCoverageIgnoreEnd

    /**
     * @return Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        if (is_null($this->_product)) {
            $this->_product = $this->getOption()->getProduct();
        }
        return $this->_product;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function saveValues()
    {
        foreach ($this->getValues() as $value) {
            $this->setData(
                $value
            )->setData(
                'option_id',
                $this->getOption()->getId()
            )->setData(
                'store_id',
                $this->getOption()->getStoreId()
            );

            if ($this->getData('is_delete') == '1') {
                if ($this->getId()) {
                    $this->deleteValues($this->getId());
                    $this->delete();
                }
            } else {
                $this->save();
            }
        }
        //eof foreach()
        return $this;
    }

    /**
     * Return price. If $flag is true and price is percent
     *  return converted percent to price
     *
     * @param bool $flag
     * @return float|int
     * @since 2.0.0
     */
    public function getPrice($flag = false)
    {
        if ($flag && $this->getPriceType() == self::TYPE_PERCENT) {
            $basePrice = $this->getOption()->getProduct()->getFinalPrice();
            $price = $basePrice * ($this->_getData(self::KEY_PRICE) / 100);
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }

    /**
     * Return regular price.
     *
     * @return float|int
     * @since 2.0.0
     */
    public function getRegularPrice()
    {
        if ($this->getPriceType() == self::TYPE_PERCENT) {
            $basePrice = $this->getOption()->getProduct()->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
            $price = $basePrice * ($this->_getData(self::KEY_PRICE) / 100);
            return $price;
        }
        return $this->_getData(self::KEY_PRICE);
    }

    /**
     * Enter description here...
     *
     * @param Option $option
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     * @since 2.0.0
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
     * @param array $optionIds
     * @param int $option_id
     * @param int $store_id
     * @return \Magento\Catalog\Model\ResourceModel\Product\Option\Value\Collection
     * @since 2.0.0
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
     * @param int $option_id
     * @return $this
     * @since 2.0.0
     */
    public function deleteValue($option_id)
    {
        $this->getResource()->deleteValue($option_id);
        return $this;
    }

    /**
     * @param int $option_type_id
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getTitle()
    {
        return $this->_getData(self::KEY_TITLE);
    }

    /**
     * Get sort order
     *
     * @return int
     * @since 2.0.0
     */
    public function getSortOrder()
    {
        return $this->_getData(self::KEY_SORT_ORDER);
    }

    /**
     * Get price type
     *
     * @return string
     * @since 2.0.0
     */
    public function getPriceType()
    {
        return $this->_getData(self::KEY_PRICE_TYPE);
    }

    /**
     * Get Sku
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSku()
    {
        return $this->_getData(self::KEY_SKU);
    }

    /**
     * Get Sku
     *
     * @return string|null
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setOptionTypeId($optionTypeId)
    {
        return $this->setData(self::KEY_OPTION_TYPE_ID, $optionTypeId);
    }

    //@codeCoverageIgnoreEnd
}
