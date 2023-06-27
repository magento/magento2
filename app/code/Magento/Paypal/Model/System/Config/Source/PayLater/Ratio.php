<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for PayLater banner ratio
 */
class Ratio implements OptionSourceInterface
{
    /**
     * PayLater ratios source
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '1x1', 'label' => '1x1'],
            ['value' => '1x4', 'label' => '1x4'],
            ['value' => '8x1', 'label' => '8x1'],
            ['value' => '20x1', 'label' => '20x1']
        ];
    }
}
