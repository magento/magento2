<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for PayLater banner text size
 */
class TextSize implements OptionSourceInterface
{
    /**
     * PayLater text sizes source
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '10', 'label' => '10px'],
            ['value' => '11', 'label' => '11px'],
            ['value' => '12', 'label' => '12px'],
            ['value' => '13', 'label' => '13px'],
            ['value' => '14', 'label' => '14px'],
            ['value' => '15', 'label' => '15px'],
            ['value' => '16', 'label' => '16px']
        ];
    }
}
