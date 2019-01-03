<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class Color
{
    /**
     * Button color source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutColor()
    {
        return [
            'gold' => __('Gold'),
            'blue' => __('Blue'),
            'silver' => __('Silver'),
            'black' => __('Black')
        ];
    }
}
