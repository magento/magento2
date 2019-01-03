<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class Size
{
    /**
     * Button size source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutSize(): array
    {
        return [
            'medium' => __('Medium'),
            'large' => __('Large'),
            'responsive' => __('Responsive')
        ];
    }
}
