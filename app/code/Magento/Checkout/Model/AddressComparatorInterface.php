<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Quote\Api\Data\AddressInterface;

interface AddressComparatorInterface
{
    /**
     * Returns true/false, if shipping address is same as billing
     *
     * @param AddressInterface|null $shippingAddress
     * @param AddressInterface|null $billingAddress
     * @return bool
     */
    public function isEqual(?AddressInterface $shippingAddress, ?AddressInterface $billingAddress): bool;
}
