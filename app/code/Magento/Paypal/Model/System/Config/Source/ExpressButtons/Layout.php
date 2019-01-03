<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class Layout
{
    /**
     * Button layout source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutLayout()
    {
        return [
            'vertical' => __('Vertical'),
            'horizontal' => __('Horizontal')
        ];
    }
}
