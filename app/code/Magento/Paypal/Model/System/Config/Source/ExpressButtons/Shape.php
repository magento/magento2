<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class Shape
{
    /**
     * Button shape source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutShape()
    {
        return [
            'pill' => __('Pill'),
            'rect' => __('Rect')
        ];
    }
}
