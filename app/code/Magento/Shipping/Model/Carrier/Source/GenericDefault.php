<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
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
