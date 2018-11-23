<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingMethodChoose;

use Magento\Quote\Model\Quote\Address;

/**
 * Provides mechanism to defer shipping method choose to the moment when quote is defined.
 *
 * @api
 * @since 100.2.0
 */
interface DeferredShippingMethodChooserInterface
{
    /**
     * Carrier code to set for deferred shipping method.
     */
    const CARRIER = 'instant-purchase';

    /**
     * Choose shipping method for a quote address.
     *
     * @param Address $address
     * @return string|null Quote shipping method code if available
     * @since 100.2.0
     */
    public function choose(Address $address);
}
