<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductOptionInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Class \Magento\Catalog\Model\ProductOption
 *
 * @since 2.0.0
 */
class ProductOption extends AbstractExtensibleModel implements ProductOptionInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\ProductOptionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
