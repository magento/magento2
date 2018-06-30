<?php

namespace Magento\Wishlist\Model\Data;

use Magento\Wishlist\Api\Data\WishlistInterface;

class Wishlist extends \Magento\Framework\Api\AbstractExtensibleObject implements WishlistInterface
{

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
     * @inheritdoc
     */
    public function getName()
    {
        $name = $this->_get(self::NAME);
        if (!strlen($name)) {
            return __(self::DEFAULT_WISHLIST_NAME);
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
        return $this->getData(self::CUSTOMER_ID);
    }
    /**
     * @inheritdoc
     */
    public function getShared()
    {
        return $this->getData(self::SHARED);
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
        return $this->getData(self::SHARING_CODE);
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
