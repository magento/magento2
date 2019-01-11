<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\ExpressButtons;

class InstallmentPeriod
{
    /**
     * Brazil button installment period source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutBrInstallmentPeriod(): array
    {
        return [
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12
        ];
    }

    /**
     * Mexico button installment period source getter for Checkout Page
     *
     * @return array
     */
    public function getCheckoutMxInstallmentPeriod(): array
    {
        return [
            3 => 3,
            6 => 6,
            9 => 9,
            12 => 12
        ];
    }
}
