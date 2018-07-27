<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model\Item;

use Magento\Catalog\Model\Product;
use Magento\Wishlist\Model\Item;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Item option model
 * @method int getProductId()
 */
class Option extends \Magento\Framework\Model\AbstractModel implements
    \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface
{
    /**
     * @var Item
     */
    protected $_item;

    /**
     * @var Product|null
     */
    protected $_product;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->productRepository = $productRepository;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Wishlist\Model\ResourceModel\Item\Option');
    }

    /**
     * Checks that item option model has data changes
     *
     * @return bool
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
     * @param   Item $item
     * @return  $this
     */
    public function setItem($item)
    {
        $this->setWishlistItemId($item->getId());
        $this->_item = $item;
        return $this;
    }

    /**
     * Get option item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Set option product
     *
     * @param   Product $product
     * @return  $this
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
     * @return Product
     */
    public function getProduct()
    {
        //In some cases product_id is present instead product instance
        if (null === $this->_product && $this->getProductId()) {
            $this->_product = $this->productRepository->getById($this->getProductId());
        }
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
            $this->setWishlistItemId($this->getItem()->getId());
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
