<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Address;

/**
 * Interface AddressInterface
 *
 * @api
 * @since 100.0.2
 */
interface AddressModelInterface
{
    /**
     * Get street line by number
     *
     * @param int $number
     * @return string
     */
    public function getStreetLine($number);

    /**
     * Create fields street1, street2, etc.
     *
     * To be used in controllers for views data
     *
     * @return $this
     */
    public function explodeStreetAddress();
}
