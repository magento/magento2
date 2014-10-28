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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Product\Option;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Option;

/**
 * Catalog product option select type model
 *
 * @method \Magento\Catalog\Model\Resource\Product\Option\Value _getResource()
 * @method \Magento\Catalog\Model\Resource\Product\Option\Value getResource()
 * @method int getOptionId()
 * @method \Magento\Catalog\Model\Product\Option\Value setOptionId(int $value)
 * @method string getSku()
 * @method \Magento\Catalog\Model\Product\Option\Value setSku(string $value)
 * @method int getSortOrder()
 * @method \Magento\Catalog\Model\Product\Option\Value setSortOrder(int $value)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Value extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Option type percent
     */
    const TYPE_PERCENT = 'percent';

    /**
     * @var array
     */
    protected $_values = array();

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
     * @var \Magento\Catalog\Model\Resource\Product\Option\Value\CollectionFactory
     */
    protected $_valueCollectionFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\Resource\Product\Option\Value\CollectionFactory $valueCollectionFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Resource\Product\Option\Value\CollectionFactory $valueCollectionFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_valueCollectionFactory = $valueCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Catalog\Model\Resource\Product\Option\Value');
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function addValue($value)
    {
        $this->_values[] = $value;
        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->_values;
    }

    /**
     * @param array $values
     * @return $this
     */
    public function setValues($values)
    {
        $this->_values = $values;
        return $this;
    }

    /**
     * @return $this
     */
    public function unsetValues()
    {
        $this->_values = array();
        return $this;
    }

    /**
     * @param Option $option
     * @return $this
     */
    public function setOption(Option $option)
    {
        $this->_option = $option;
        return $this;
    }

    /**
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
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_product = $product;
        return $this;
    }

    /**
     * @return Product
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

            if ($this->getData('option_type_id') == '-1') {
                //change to 0
                $this->unsetData('option_type_id');
            } else {
                $this->setId($this->getData('option_type_id'));
            }

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
     */
    public function getPrice($flag = false)
    {
        if ($flag && $this->getPriceType() == self::TYPE_PERCENT) {
            $basePrice = $this->getOption()->getProduct()->getFinalPrice();
            $price = $basePrice * ($this->_getData('price') / 100);
            return $price;
        }
        return $this->_getData('price');
    }

    /**
     * Enter description here...
     *
     * @param Option $option
     * @return \Magento\Catalog\Model\Resource\Product\Option\Value\Collection
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
     * @return \Magento\Catalog\Model\Resource\Product\Option\Value\Collection
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
     */
    public function deleteValue($option_id)
    {
        $this->getResource()->deleteValue($option_id);
        return $this;
    }

    /**
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
}
