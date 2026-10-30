<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

class ProductOption extends AbstractExtensibleModel implements ProductOptionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductOptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
