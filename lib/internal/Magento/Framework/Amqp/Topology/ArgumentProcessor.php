<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp\Topology;

use InvalidArgumentException;

/**
 * @deprecated 103.0.0
 * see: https://github.com/php-amqplib/php-amqplib/issues/405
 */
trait ArgumentProcessor
{
    /**
     * Process arguments
     *
     * @param array $arguments
     * @return array
     */
    public function processArguments($arguments): array
    {
        $output = [];
        foreach ($arguments as $key => $value) {
            if (is_array($value)) {
                $output[$key] = ['A', $value];
            } elseif (is_numeric($value)) {
                $output[$key] = ['I', (int) $value];
            } elseif (is_bool($value)) {
                $output[$key] = ['t', $value];
            } elseif (is_string($value)) {
                $output[$key] = ['S', $value];
            } else {
                throw new InvalidArgumentException('Unknown argument type ' . gettype($value));
            }
        }

        return $output;
    }
}
