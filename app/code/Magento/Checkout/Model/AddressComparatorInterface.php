<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Checkout\Model;

use Magento\Quote\Api\Data\AddressInterface;

interface AddressComparatorInterface
{
    /**
     * Returns true/false, after addresses comparison
     *
     * @param AddressInterface|null $address1
     * @param AddressInterface|null $address2
     * @return bool
     */
    public function isEqual(?AddressInterface $address1, ?AddressInterface $address2): bool;
}
