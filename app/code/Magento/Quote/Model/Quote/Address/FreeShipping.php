<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address;

/**
 * Class \Magento\Quote\Model\Quote\Address\FreeShipping
 *
 * @since 2.0.0
 */
class FreeShipping implements \Magento\Quote\Model\Quote\Address\FreeShippingInterface
{
    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function isFreeShipping(\Magento\Quote\Model\Quote $quote, $items)
    {
        return false;
    }
}
