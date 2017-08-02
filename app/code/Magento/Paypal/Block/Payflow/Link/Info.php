<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Payflow\Link;

/**
 * @deprecated 2.2.0 This class should not be used because parent class can handle client calls.
 *             Class was not removed for backward compatibility.
 * @see \Magento\Paypal\Block\Payment\Info
 * @since 2.0.0
 */
class Info extends \Magento\Paypal\Block\Payment\Info
{
    /**
     * Don't show CC type
     *
     * @return false
     * @deprecated 2.2.0 unused
     * @since 2.0.0
     */
    public function getCcTypeName()
    {
        return false;
    }
}
