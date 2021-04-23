<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

class Position
{
    /**
     * PayLater positions source getter for Catalog Product Page
     *
     * @return array
     */
    public function getPositionsCPP(): array
    {
        return [
            '0' => __('Header (center)'),
            '1' => __('Near PayPal Credit checkout button')
        ];
    }
}
