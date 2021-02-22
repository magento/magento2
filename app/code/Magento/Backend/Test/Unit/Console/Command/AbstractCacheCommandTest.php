<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\AbstractCacheManageCommand;

abstract class AbstractCacheCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $cacheManagerMock;

    /**
     * @var AbstractCacheManageCommand
     */
    protected $command;

    protected function setUp(): void
    {
        $this->cacheManagerMock = $this->createMock(\Magento\Framework\App\Cache\Manager::class);
    }

    /**
     * Formats expected output for testExecute data providers
     *
     * @param array $types
     * @return string
     */
    abstract public function getExpectedExecutionOutput(array $types);
}
