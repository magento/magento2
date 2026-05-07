<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Dhl\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Provides available DHL integration types as options
 * for Magento system configuration fields.
 */
class Type implements OptionSourceInterface
{
    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'DHL_XML', 'label' => __('DHL XML')],
            ['value' => 'DHL_REST', 'label' => __('DHL REST')]
        ];
    }
}
