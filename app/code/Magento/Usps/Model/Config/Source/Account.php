<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Usps\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Account implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'EPS', 'label' => __('EPS')],
            ['value' => 'PERMIT', 'label' => __('PERMIT')]
        ];
    }
}
