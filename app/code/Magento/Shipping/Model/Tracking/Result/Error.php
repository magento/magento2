<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
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
