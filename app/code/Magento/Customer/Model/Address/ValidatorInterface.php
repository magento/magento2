<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
