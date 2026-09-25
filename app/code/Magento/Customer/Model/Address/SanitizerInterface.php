<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address;

interface SanitizerInterface
{
    /**
     * Sanitize address instance.
     *
     * @param AbstractAddress $address
     * @return AbstractAddress
     * @since 102.0.0
     */
    public function sanitize(AbstractAddress $address): AbstractAddress;
}
