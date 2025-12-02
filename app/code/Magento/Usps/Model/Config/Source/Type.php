<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Usps\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Type implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'USPS_XML', 'label' => __('USPS Web Tools API')],
            ['value' => 'USPS_REST', 'label' => __('USPS Rest APIs')]
        ];
    }
}
