<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\Address;

interface SanitizerInterface
{
    /**
     * Sanitize address instance.
     *
     * @param AbstractAddress $address
     * @return AbstractAddress $address
     * @since 102.0.0
     */
    public function sanitize(AbstractAddress $address): AbstractAddress;
}
