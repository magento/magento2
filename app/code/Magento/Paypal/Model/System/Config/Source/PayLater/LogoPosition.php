<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

/**
 * Source model for PayLater banner logo position
 */
class LogoPosition
{
    /**
     * PayLater logo positions source getter for Catalog Product Page
     *
     * @return array
     */
    public function getLogoPositionsCPP(): array
    {
        return [
            'left' => __('Left'),
            'right' => __('Right'),
            'top' => __('Top')
        ];
    }
}
