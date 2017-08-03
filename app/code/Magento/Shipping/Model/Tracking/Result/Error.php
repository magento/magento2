<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Model\Tracking\Result;

/**
 * Class \Magento\Shipping\Model\Tracking\Result\Error
 *
 * @since 2.0.0
 */
class Error extends \Magento\Shipping\Model\Tracking\Result\AbstractResult
{
    /**
     * @return array
     * @since 2.0.0
     */
    public function getAllData()
    {
        return $this->_data;
    }

    /**
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getErrorMessage()
    {
        return __('Tracking information is unavailable.');
    }
}
