<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

/**
 * Source model for PayLater banner logo type
 */
class LogoType
{
    /**
     * PayLater logo types source getter for Catalog Product Page
     *
     * @return array
     */
    public function getLogoTypesCPP(): array
    {
        return [
            'primary' => __('Primary'),
            'alternative' => __('Alternative'),
            'inline' => __('Inline'),
            'none' => __('None')
        ];
    }
}
