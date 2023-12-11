<?php
declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model\Tracking\Result;

/**
 * Tracking Status DataObject
 *
 * @method string|null getCarrier()
 * @method Status setCarrier(string $carrierCode)
 * @method string|null getCarrierTitle()
 * @method Status setCarrierTitle(string $carrierTitle)
 */
class Status extends AbstractResult
{
    public const STATUS_TYPE = 0;

    /**
     * Returns all Status data
     *
     * @return array
     */
    public function getAllData(): array
    {
        return $this->_data;
    }
}
