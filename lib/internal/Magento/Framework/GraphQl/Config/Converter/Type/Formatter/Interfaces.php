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
     * Format of input entry should conform to the following structure for interfaces that output type implements to be
     * processed correctly:
     * ['implements' => [ // Required
     *     $indexOfImplementedInterface => [
     *         'interface' => $nameOfInterfaceType, // Required
     *         'copyFields' => $shouldCopyFields // Optional - should be string that says "true" or "false"
     *     ],
     *     .
     *     .
     *     .
     * ]
     *
     * Format of output entry for interfaces that type implements is as follows:
     * ['implements' => [
     *     $interfaceName => [
     *         'interface' => $interfaceName,
     *         'copyFields' => $shouldCopyFields // Present only if given in input, string that says "true" or "false"
     *     ],
     *     .
     *     .
     *     .
     * ]
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
