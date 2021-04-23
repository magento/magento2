<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

class StyleLayout
{
    /**
     * PayLater style layouts source getter for Catalog Product Page
     *
     * @return array
     */
    public function getStyleLayoutsCPP(): array
    {
        return [
            'text' => __('Text'),
            'flex' => __('Flex')
        ];
    }
}
