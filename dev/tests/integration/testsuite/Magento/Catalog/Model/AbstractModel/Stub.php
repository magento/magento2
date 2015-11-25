<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\AbstractModel;

abstract class Stub extends \Magento\Catalog\Model\AbstractModel implements \Magento\Catalog\Api\Data\ProductInterface
{
    /**
     * Retrieve Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(\Magento\Catalog\Model\Product::STORE_ID);
    }

    /**
     * Set product store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(\Magento\Catalog\Model\Product::STORE_ID, $storeId);
    }
}
