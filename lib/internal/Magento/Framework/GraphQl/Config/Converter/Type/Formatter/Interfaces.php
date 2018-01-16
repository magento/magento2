<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Converter\Type\Formatter;

use Magento\Framework\GraphQl\Config\Converter\Type\FormatterInterface;

/**
 * Add interfaces to a type if declared.
 */
class Interfaces implements FormatterInterface
{
    /**
     * {@inheritDoc}
     */
    public function format(array $entry): array
    {
        if (!empty($entry['implements'])) {
            $implements = [];
            foreach ($entry['implements'] as $interface) {
                $implements['implements'][$interface['interface']] = $interface;
            }
            return $implements;
        } else {
            return [];
        }
    }
}
