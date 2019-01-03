<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class Color
{
    /**
     * Button color source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutColor(): array
    {
        return [
            'gold' => __('Gold'),
            'blue' => __('Blue'),
            'silver' => __('Silver'),
            'black' => __('Black')
        ];
    }
}
