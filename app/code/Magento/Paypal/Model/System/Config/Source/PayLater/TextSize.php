<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

/**
 * Source model for PayLater banner text size
 */
class TextSize
{
    /**
     * PayLater text sizes source getter for Catalog Product Page
     *
     * @return array
     */
    public function getTextSizesCPP(): array
    {
        return [
            '10' => '10px',
            '11' => '11px',
            '12' => '12px',
            '13' => '13px',
            '14' => '14px',
            '15' => '15px',
            '16' => '16px'
        ];
    }
}
