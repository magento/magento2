<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Carrier;

/**
 * Interface AbstractCarrierInterface
 *
 * @deprecated
 * @see CarrierInterface
 */
interface AbstractCarrierInterface extends CarrierInterface
{
    /**
     * Is state province required
     *
     * @return bool
     */
    public function isStateProvinceRequired();

    /**
     * Check if city option required
     *
     * @return bool
     */
    public function isCityRequired();

    /**
     * Used to call debug method from not Payment Method context
     *
     * @param mixed $debugData
     * @return void
     */
    public function debugData($debugData);
}
