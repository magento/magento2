<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Model\System\Config\Source\PayLater;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for PayLater banner style layout
 */
class StyleLayout implements OptionSourceInterface
{
    /**
     * PayLater style layouts source
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'text', 'label' => __('Text')],
            ['value' => 'flex', 'label' => __('Flex')]
        ];
    }
}
