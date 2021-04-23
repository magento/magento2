<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

class Color
{
    /**
     * PayLater colors source getter for Catalog Product Page
     *
     * @return array
     */
    public function getColorsCPP(): array
    {
        return [
            'blue' => __('Blue'),
            'black' => __('Black'),
            'white' => __('White'),
            'white-no-border' => __('White No Border'),
            'gray' => __('Gray')
        ];
    }
}
