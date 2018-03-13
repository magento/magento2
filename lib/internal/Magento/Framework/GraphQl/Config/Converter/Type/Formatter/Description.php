<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Converter\Type\Formatter;

use Magento\Framework\GraphQl\Config\Converter\Type\FormatterInterface;

/**
 * Format description of type if present.
 */
class Description implements FormatterInterface
{
    /**
     * {@inheritDoc}
     * Input format:
     * ['description' => $descriptionString]
     *
     * Output format:
     * ['description' => $descriptionString] or []
     */
    public function format(array $entry): array
    {
        if (isset($entry['description'])) {
            return ['description' => $entry['description']];
        } else {
            return [];
        }
    }
}
