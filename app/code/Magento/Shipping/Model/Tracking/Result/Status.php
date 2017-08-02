<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Tracking\Result;

/**
 * Fields:
 * - carrier: carrier code
 * - carrierTitle: carrier title
 * @since 2.0.0
 */
class Status extends \Magento\Shipping\Model\Tracking\Result\AbstractResult
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function getAllData()
    {
        return $this->_data;
    }
}
