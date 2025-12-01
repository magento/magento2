<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\Address;

/**
 * Interface for address validator.
 *
 * @api
 * @since 102.0.0
 */
interface ValidatorInterface
{
    /**
     * Validate address instance.
     * Return array of errors if not valid.
     *
     * @param AbstractAddress $address
     * @return array
     * @since 102.0.0
     */
    public function validate(AbstractAddress $address);
}
