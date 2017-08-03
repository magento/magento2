<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote\Address\RateResult;

/**
 * Class \Magento\Quote\Model\Quote\Address\RateResult\Error
 *
 * @since 2.0.0
 */
class Error extends AbstractResult
{
    /**
     * @return mixed
     * @since 2.0.0
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
