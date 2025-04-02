<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

abstract class AbstractCacheSetCommandTestCase extends AbstractCacheManageCommandTestCase
{
    /**
     * @return array
     */
    public static function executeDataProvider()
    {
        return [
            'implicit all' => [
                [],
                ['A', 'B', 'C'],
                ['A', 'B', 'C'],
                static::getExpectedExecutionOutput(['A', 'B', 'C']),
            ],
            'specified types' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                ['A', 'B'],
                static::getExpectedExecutionOutput(['A', 'B']),
            ],
            'no changes' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                [],
                static::getExpectedExecutionOutput([]),
            ],
        ];
    }

    /**
     * Formats expected output of cache status change
     *
     * @param array $changes
     * @param bool $enabled
     * @return string
     */
    public static function getExpectedChangeOutput(array $changes, $enabled)
    {
        if ($changes) {
            $output = 'Changed cache status:' . PHP_EOL;
            foreach ($changes as $type) {
                $output .= sprintf('%30s: %d -> %d', $type, $enabled === false, $enabled === true) . PHP_EOL;
            }
        } else {
            $output = 'There is nothing to change in cache status' . PHP_EOL;
        }
        return $output;
    }
}
