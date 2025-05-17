<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Framework\Event\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCacheManageCommandTestCase extends AbstractCacheCommandTestCase
{
    /** @var  string */
    protected $cacheEventName;

    /** @var  ManagerInterface|MockObject */
    protected $eventManagerMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        parent::setUp();
    }

    /**
     * @return array
     */
    public static function executeDataProvider()
    {
        return [
            'implicit all' => [
                [],
                ['A', 'B', 'C', 'full_page'],
                true,
                static::getExpectedExecutionOutput(['A', 'B', 'C', 'full_page']),
            ],
            'specified types' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                false,
                static::getExpectedExecutionOutput(['A', 'B']),
            ],
            'fpc_only' => [
                ['types' => ['full_page']],
                ['full_page'],
                true,
                static::getExpectedExecutionOutput(['full_page']),
            ],
        ];
    }

    public function testExecuteInvalidCacheType()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The following requested cache types are not supported:');
        $this->cacheManagerMock->expects($this->once())->method('getAvailableTypes')->willReturn(['A', 'B', 'C']);
        $param = ['types' => ['A', 'D']];
        $commandTester = new CommandTester($this->command);
        $commandTester->execute($param);
    }
}
