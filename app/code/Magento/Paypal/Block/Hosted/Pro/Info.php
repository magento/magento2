<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Hosted Pro link infoblock
 */
namespace Magento\Paypal\Block\Hosted\Pro;

class Info extends \Magento\Paypal\Block\Payment\Info
{
    /**
     * Don't show CC type
     *
     * @return false
     */
    public function getCcTypeName()
    {
        return false;
    }
}
