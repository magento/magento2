<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Model\Item;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Wishlist\Api\Data\ItemInterface;
use Magento\Wishlist\Api\Data\OptionInterface;
use Magento\Wishlist\Model\Item;

/**
 * Item option model
 *
 * @api
 * @since 100.0.2
 */
class Option extends \Magento\Framework\Model\AbstractModel implements OptionInterface
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
        $this->_init(\Magento\Wishlist\Model\ResourceModel\Item\Option::class);
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData($this->getIdFieldName());
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     *{@inheritdoc}
     */
    public function setItem(ItemInterface $item)
    {
        $this->setWishlistItemId($item->getId());
        $this->_item = $item;
        return $this;
    }

    /**
     *{@inheritdoc}
     */
    public function getItem(): ItemInterface
    {
        return $this->_item;
    }

    /**
     *{@inheritdoc}
     */
    public function setProduct(\Magento\Catalog\Api\Data\ProductInterface $product)
    {
        $this->setProductId($product->getId());
        $this->_product = $product;
        return $this;
    }

    /**
     *{@inheritdoc}
     */
    public function getProduct(): \Magento\Catalog\Api\Data\ProductInterface
    {
        //In some cases product_id is present instead product instance
        if (null === $this->_product && $this->getProductId()) {
            $this->_product = $this->productRepository->getById($this->getProductId());
        }
        return $this->_product;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->_getData(self::VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this->setData(self::VALUE, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
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
