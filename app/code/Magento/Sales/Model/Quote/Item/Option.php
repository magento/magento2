<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote\Item;

/**
 * Item option model
 *
 * @method \Magento\Sales\Model\Resource\Quote\Item\Option _getResource()
 * @method \Magento\Sales\Model\Resource\Quote\Item\Option getResource()
 * @method int getItemId()
 * @method \Magento\Sales\Model\Quote\Item\Option setItemId(int $value)
 * @method int getProductId()
 * @method \Magento\Sales\Model\Quote\Item\Option setProductId(int $value)
 * @method string getCode()
 * @method \Magento\Sales\Model\Quote\Item\Option setCode(string $value)
 * @method \Magento\Sales\Model\Quote\Item\Option setValue(string $value)
 */
class Option extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
{
    /**
     * @var \Magento\Sales\Model\Quote\Item
     */
    protected $_item;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Quote\Item\Option');
    }

    /**
     * Checks that item option model has data changes
     *
     * @return boolean
     */
    protected function _hasModelChanged()
    {
        if (!$this->hasDataChanges()) {
            return false;
        }

        return $this->_getResource()->hasDataChanged($this);
    }

    /**
     * Set quote item
     *
     * @param   \Magento\Sales\Model\Quote\Item $item
     * @return $this
     */
    public function setItem($item)
    {
        $this->setItemId($item->getId());
        $this->_item = $item;
        return $this;
    }

    /**
     * Get option item
     *
     * @return \Magento\Sales\Model\Quote\Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Set option product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->setProductId($product->getId());
        $this->_product = $product;
        return $this;
    }

    /**
     * Get option product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Get option value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->_getData('value');
    }

    /**
     * Initialize item identifier before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getItem()) {
            $this->setItemId($this->getItem()->getId());
        }
        return parent::beforeSave();
    }

    /**
     * Clone option object
     *
     * @return $this
     */
    public function __clone()
    {
        $this->setId(null);
        $this->_item = null;
        return $this;
    }
}
