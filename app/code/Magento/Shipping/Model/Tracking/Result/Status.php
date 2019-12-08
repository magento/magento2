<?php
declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model\Tracking\Result;

/**
 * @method string getCarrier()
 * @method Status setCarrier(string $carrierCode)
 * @method string getCarrierTitle()
 * @method Status setCarrierTitle(string $carrierTitle)
 */
class Status extends AbstractResult
{
    /**
     * @return array
     */
    public function getAllData(): array
    {
        return $this->_data;
    }
}
