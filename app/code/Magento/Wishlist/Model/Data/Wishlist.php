<?php

namespace Magento\Wishlist\Model\Data;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Wishlist\Api\Data\ItemInterface;
use Magento\Wishlist\Api\Data\WishlistInterface;
use Magento\Wishlist\Model\ResourceModel\Item\CollectionFactory;
use Magento\Wishlist\Model\WishlistFactory;

class Wishlist extends \Magento\Framework\Api\AbstractExtensibleObject implements WishlistInterface
{
    /**
     * @var WishlistFactory
     */
    private $wishlistFactory;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var ItemInterface[];
     */
    private $items;

    /**
     * Wishlist constructor.
     * @param ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $attributeValueFactory
     * @param array $data
     * @param WishlistFactory $wishlistFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $attributeValueFactory,
        array $data = [],
        WishlistFactory $wishlistFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($extensionFactory, $attributeValueFactory, $data);
        $this->wishlistFactory = $wishlistFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_get(self::WISHLIST_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::WISHLIST_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if ($this->items === null) {

            /*
             * We create wishlist because addWishlistFilter() expects legacy wishlist model just to call getId() on it.
             */
            $wishlist = $this->wishlistFactory->create()->setId($this->getId());

            $this->items = $this->collectionFactory->create()->addWishlistFilter(
                $wishlist
            )->addStoreFilter(
                $this->wishlistFactory->create()->getSharedStoreIds()
            )->setVisibilityFilter()->toArray();
        }

        return $this->items;

    }


    /**
     * @inheritdoc
     */
    public function getName()
    {
        $name = $this->_get(self::NAME);
        if (!strlen($name)) {
            return self::DEFAULT_WISHLIST_NAME;
        }
        return $name;
    }

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }


    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function getShared()
    {
        return $this->_get(self::SHARED);
    }

    /**
     * @inheritdoc
     */
    public function setShared(int $amount)
    {
        return $this->setData(self::SHARED, $amount);
    }

    /**
     * @inheritdoc
     */
    public function getSharingCode()
    {
        return $this->_get(self::SHARING_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setSharingCode(string $code)
    {
        return $this->setData(self::SHARING_CODE, $code);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(\Magento\Wishlist\Api\Data\WishlistExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
