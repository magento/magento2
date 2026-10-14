<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Setup\Patch\Data;

use Magento\Customer\Setup\Patch\Data\SessionIDColumnCleanUp;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SessionIDColumnCleanUpTest extends TestCase
{
    public function testClearsSessionIdsInBatchesOfOneThousand(): void
    {
        $select = $this->createMock(Select::class);
        $select->method('from')->willReturnSelf();
        $select->method('where')->willReturnSelf();
        $select->expects($this->exactly(2))->method('limit')->with(1000)->willReturnSelf();

        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('select')->willReturn($select);
        $connection->method('fetchCol')->willReturnOnConsecutiveCalls([10, 11], []);
        $connection->expects($this->once())
            ->method('update')
            ->with(
                'customer_visitor',
                $this->callback(static function (array $bind): bool {
                    return isset($bind['session_id']) && (string) $bind['session_id'] === 'NULL';
                }),
                ['visitor_id IN (?)' => [10, 11]]
            )
            ->willReturn(2);

        $setup = $this->createMock(ModuleDataSetupInterface::class);
        $setup->method('getConnection')->willReturn($connection);
        $setup->method('getTable')->with('customer_visitor')->willReturn('customer_visitor');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');

        $patch = new SessionIDColumnCleanUp($setup, $logger);
        $this->assertSame($patch, $patch->apply());
    }
}
