<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model\ShippingMethodChoose;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;
use Magento\Quote\Api\Data\ShippingMethodInterface;

/**
 * Interface to choose shipping method for customer address if available.
 *
 * If chooser return existed shipping method it will be used to place order.
 *
 * Instant purchase supports deferred shipping method choose that allow select method after quote creation.
 * To implement deferred choose return ShippingMethodInterface instance with carrier code equals to
 * DeferredShippingMethodChooserInterface::CARRIER and shipping method code as a key to deferred chooser registered in
 * DeferredShippingMethodChooserPool.
 *
 * @api
 * @since 100.2.0
 */
interface ShippingMethodChooserInterface
{
    /**
     * @param Address $address
     * @return ShippingMethodInterface|null
     * @since 100.2.0
     */
    public function choose(Address $address);
}
