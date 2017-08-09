<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Carrier\Source;

/**
 * Class GenericDefault
 * Default implementation of generic carrier source
 *
 */
class GenericDefault implements GenericInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [];
    }
}
