<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\CostInterface;

/**
 * Product Cost DTO.
 * @since 2.2.0
 */
class Cost extends \Magento\Framework\Model\AbstractExtensibleModel implements CostInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setCost($cost)
    {
        return $this->setData(self::COST, $cost);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getCost()
    {
        return $this->getData(self::COST);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setExtensionAttributes(\Magento\Catalog\Api\Data\CostExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
