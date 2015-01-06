<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Shipping\Model\Tracking\Result;

class Error extends \Magento\Shipping\Model\Tracking\Result\AbstractResult
{
    /**
     * @return array
     */
    public function getAllData()
    {
        return $this->_data;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return __('Tracking information is unavailable.');
    }
}
