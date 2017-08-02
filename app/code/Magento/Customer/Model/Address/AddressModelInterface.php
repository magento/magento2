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
 * @since 2.0.0
 */
interface AddressModelInterface
{
    /**
     * Get steet line by number
     *
     * @param int $number
     * @return string
     * @since 2.0.0
     */
    public function getStreetLine($number);

    /**
     * Create fields street1, street2, etc.
     *
     * To be used in controllers for views data
     *
     * @return $this
     * @since 2.0.0
     */
    public function explodeStreetAddress();
}
