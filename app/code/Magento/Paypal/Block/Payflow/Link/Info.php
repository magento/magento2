<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Payflow link infoblock
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Paypal\Block\Payflow\Link;

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
