<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class Layout
{
    /**
     * Button layout source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutLayout(): array
    {
        return [
            'vertical' => __('Vertical'),
            'horizontal' => __('Horizontal')
        ];
    }
}
