<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

class TextColor
{
    /**
     * PayLater text colors source getter for Catalog Product Page
     *
     * @return array
     */
    public function getTextColorsCPP(): array
    {
        return [
            'black' => __('Black'),
            'white' => __('White')
        ];
    }
}
