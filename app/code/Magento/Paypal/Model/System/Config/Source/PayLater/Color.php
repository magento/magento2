<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for PayLater flex banner color
 */
class Color implements OptionSourceInterface
{
    /**
     * PayLater colors source
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'blue', 'label' => __('Blue')],
            ['value' => 'black', 'label' => __('Black')],
            ['value' => 'white', 'label' => __('White')],
            ['value' => 'white-no-border', 'label' => __('White No Border')],
            ['value' => 'gray', 'label' => __('Gray')],
            ['value' => 'monochrome', 'label' => __('Monochrome')],
            ['value' => 'grayscale', 'label' => __('Grayscale')]
        ];
    }
}
