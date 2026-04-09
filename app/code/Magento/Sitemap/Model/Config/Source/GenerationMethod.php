<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sitemap\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for sitemap generation method configuration
 */
class GenerationMethod implements OptionSourceInterface
{
    /**
     * Standard generation method constant
     */
    public const STANDARD = 'standard';

    /**
     * Batch generation method constant
     */
    public const BATCH = 'batch';

    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::STANDARD, 'label' => __('Standard')],
            ['value' => self::BATCH, 'label' => __('Batch (Memory Optimized)')],
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::STANDARD => __('Standard'),
            self::BATCH => __('Batch (Memory Optimized)'),
        ];
    }
}
