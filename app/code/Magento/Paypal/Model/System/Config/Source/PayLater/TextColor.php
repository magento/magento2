<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for PayLater banner text color
 */
class TextColor implements OptionSourceInterface
{
    /**
     * PayLater text colors source
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'black', 'label' => __('Black')],
            ['value' => 'white', 'label' => __('White')],
            ['value' => 'monochrome', 'label' => __('Monochrome')],
            ['value' => 'grayscale', 'label' => __('Grayscale')]
        ];
    }
}
