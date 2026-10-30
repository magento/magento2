<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\AbstractModel;

/**
 * Concrete stub class for testing AbstractModel
 */
class ConcreteStub extends \Magento\Catalog\Model\AbstractModel
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
