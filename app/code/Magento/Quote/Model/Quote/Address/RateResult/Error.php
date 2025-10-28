<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Quote\Model\Quote\Address\RateResult;

class Error extends AbstractResult
{
    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        if (!$this->getData('error_message')) {
            $this->setData(
                'error_message',
                __('This shipping method is not available. To use this shipping method, please contact us.')
            );
        }
        return $this->getData('error_message');
    }
}
