<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Console\Command;

use Magento\Framework\Event\ManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Console\Tester\CommandTester;

abstract class AbstractCacheManageCommandTest extends AbstractCacheCommandTest
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
    public function executeDataProvider()
    {
        return [
            'implicit all' => [
                [],
                ['A', 'B', 'C'],
                $this->getExpectedExecutionOutput(['A', 'B', 'C']),
            ],
            'specified types' => [
                ['types' => ['A', 'B']],
                ['A', 'B'],
                $this->getExpectedExecutionOutput(['A', 'B']),
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
