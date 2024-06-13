<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
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
