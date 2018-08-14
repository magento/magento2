<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

abstract class AbstractCacheSetCommandTest extends AbstractCacheManageCommandTest
{
    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'implicit all' => [
                [],
                ['A', 'B', 'C'],
                ['A', 'B', 'C'],
                $this->getExpectedExecutionOutput(['A', 'B', 'C']),
            ],
            'specified types' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                ['A', 'B'],
                $this->getExpectedExecutionOutput(['A', 'B']),
            ],
            'no changes' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                [],
                $this->getExpectedExecutionOutput([]),
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
    public function getExpectedChangeOutput(array $changes, $enabled)
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
