<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

/**
 * Source model for PayLater banner ratio
 */
class Ratio
{
    /**
     * PayLater ratios source getter for Catalog Product Page
     *
     * @return array
     */
    public function getRatiosCPP(): array
    {
        return [
            '1x1' => '1x1',
            '1x4' => '1x4',
            '8x1' => '8x1',
            '20x1' => '20x1'
        ];
    }
}
