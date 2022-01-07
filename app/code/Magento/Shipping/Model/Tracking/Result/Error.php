<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Tracking\Result;

use Magento\Framework\Phrase;

/**
 * Class to get data from error shipping tracking result
 */
class Error extends AbstractResult
{
    public const STATUS_TYPE = 1;

    /**
     * Gets all data of shipping tracking result
     *
     * @return array
     */
    public function getAllData()
    {
        return $this->_data;
    }

    /**
     * Gets error message
     *
     * @return Phrase
     */
    public function getErrorMessage()
    {
        return __('Tracking information is unavailable.');
    }
}
