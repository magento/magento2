<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source model for New Relic API mode selection
 */
class ApiMode implements OptionSourceInterface
{
    /**
     * API mode constants
     */
    public const MODE_V2_REST = 'v2_rest';
    public const MODE_NERDGRAPH = 'nerdgraph';

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::MODE_V2_REST,
                'label' => __('v2 REST (Legacy)')
            ],
            [
                'value' => self::MODE_NERDGRAPH,
                'label' => __('NerdGraph (GraphQL) - Recommended')
            ]
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
            self::MODE_V2_REST => __('v2 REST (Legacy)'),
            self::MODE_NERDGRAPH => __('NerdGraph (GraphQL) - Recommended')
        ];
    }
}
