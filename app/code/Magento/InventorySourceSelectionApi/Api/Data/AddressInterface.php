<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

/**
 * Data interface for shipping source or shipping destination
 *
 * @api
 */
interface AddressInterface
{
    /**
     * Requested shipping country
     *
     * @return string
     */
    public function getCountry(): string;

    /**
     * Requested shipping postcode
     *
     * @return string
     */
    public function getPostcode(): string;

    /**
     * Requested shipping street address
     *
     * @return string
     */
    public function getStreet(): string;

    /**
     * Requested shipping region
     *
     * @return string
     */
    public function getRegion(): string;

    /**
     * Requested shipping city
     *
     * @return string
     */
    public function getCity(): string;
}
