<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Backend\Console\Command\AbstractCacheManageCommand;
use Magento\Framework\App\Cache\Manager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractCacheCommandTestCase extends TestCase
{
    /**
     * @var Manager|MockObject
     */
    protected $cacheManagerMock;

    /**
     * @var AbstractCacheManageCommand
     */
    protected $command;

    protected function setUp(): void
    {
        $this->cacheManagerMock = $this->createMock(Manager::class);
    }

    /**
     * Formats expected output for testExecute data providers
     *
     * @param array $types
     * @return string
     */
    abstract public static function getExpectedExecutionOutput(array $types);
}
