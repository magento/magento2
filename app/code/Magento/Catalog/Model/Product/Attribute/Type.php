<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

/**
 * @codeCoverageIgnore
 */
class Type extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Catalog\Api\Data\ProductAttributeTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->getData('value');
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }
}
