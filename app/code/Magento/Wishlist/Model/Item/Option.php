<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Model\Item;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Wishlist\Model\Item;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Wishlist\Model\ResourceModel\Item\Option as ResourceOption;
use Psr\Log\LoggerInterface;

/**
 * Item option model
 *
 * @method int getProductId()
 * @api
 * @since 100.0.2
 */
class Option extends AbstractModel implements OptionInterface
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
     * @param Context $context
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected readonly ProductRepositoryInterface $productRepository,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        private ?LoggerInterface $logger = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceOption::class);
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
            try {
                $this->_product = $this->productRepository->getById($this->getProductId());
            } catch (NoSuchEntityException $exception) {
                $this->logger->error($exception);
            }
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
