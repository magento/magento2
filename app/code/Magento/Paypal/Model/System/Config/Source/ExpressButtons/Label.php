<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class Label
{
    /**
     * Button label source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutLabel()
    {
        return [
            'checkout' => __('Checkout'),
            'credit' => __('Credit'),
            'pay' => __('Pay'),
            'buynow' => __('Buynow'),
            'paypal' => __('Paypal'),
            'installment' => __('Installment')
        ];
    }
}
