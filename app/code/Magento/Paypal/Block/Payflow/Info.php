<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Paypal\Block\Payflow;

/**
 * @deprecated 100.2.0 This class should not be used because parent class can handle client calls.
 *             Class was not removed for backward compatibility.
 * @see \Magento\Paypal\Block\Payment\Info
 */
class Info extends \Magento\Paypal\Block\Payment\Info
{
    /**
     * Don't show CC type
     *
     * @return false
     * @deprecated 100.2.0 unused
     */
    public function getCcTypeName()
    {
        return false;
    }
}
