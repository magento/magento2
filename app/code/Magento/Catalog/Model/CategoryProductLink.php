<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model;

/**
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class CategoryProductLink extends \Magento\Framework\Api\AbstractExtensibleObject implements
    \Magento\Catalog\Api\Data\CategoryProductLinkInterface
{
    /**#@+
     * Constant for confirmation status
     */
    const KEY_SKU = 'sku';
    const KEY_POSITION = 'position';
    const KEY_CATEGORY_ID = 'category_id';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getSku()
    {
        return $this->_get(self::KEY_SKU);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPosition()
    {
        return $this->_get(self::KEY_POSITION);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getCategoryId()
    {
        return $this->_get(self::KEY_CATEGORY_ID);
    }

    /**
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku)
    {
        return $this->setData(self::KEY_SKU, $sku);
    }

    /**
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position)
    {
        return $this->setData(self::KEY_POSITION, $position);
    }

    /**
     * Set category id
     *
     * @param string $categoryId
     * @return $this
     * @since 2.0.0
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::KEY_CATEGORY_ID, $categoryId);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Catalog\Api\Data\CategoryProductLinkExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Catalog\Api\Data\CategoryProductLinkExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\CategoryProductLinkExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
