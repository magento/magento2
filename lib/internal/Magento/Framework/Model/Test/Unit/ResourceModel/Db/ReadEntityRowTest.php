<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\ReadEntityRow;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for ReadEntityRow class.
 */
class ReadEntityRowTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var ReadEntityRow
     */
    protected $subject;

    /**
     * @var Select|MockObject
     */
    protected $select;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->select = $this->createMock(Select::class);

        $this->connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        $this->connection->expects($this->any())
            ->method('select')
            ->willReturn($this->select);

        $this->connection->expects($this->any())
            ->method('quoteIdentifier')
            ->willReturnArgument(0);

        $metadata = $this->createMock(EntityMetadata::class);

        $metadata->expects($this->any())
            ->method('getEntityTable')
            ->willReturn('entity_table');

        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

        $metadata->expects($this->any())
            ->method('getIdentifierField')
            ->willReturn('identifier');

        $this->metadataPool = $this->createMock(MetadataPool::class);

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with('Test\Entity\Type')
            ->willReturn($metadata);

        $this->subject = new ReadEntityRow(
            $this->metadataPool
        );
    }

    /**
     * @return void
     */
    public function testExecute(): void
    {
        $identifier = '100000001';

        $context = ['store_id' => 1];
        $expectedData = ['entity_id' => 1];

        $this->select->expects($this->once())
            ->method('from')
            ->with(['t' => 'entity_table'])
            ->willReturnSelf();

        $this->select
            ->method('where')
            ->willReturnCallback(
                function ($arg1, $arg2) use ($identifier) {
                    if ($arg1 == 'identifier = ?' && $arg2 == $identifier) {
                        return $this->select;
                    } elseif ($arg1 == 'store_id = ?' && $arg2 == 1) {
                        return $this->select;
                    }
                }
            );

        $this->connection->expects($this->once())
            ->method('fetchRow')
            ->willReturn($expectedData);

        $actualData = $this->subject->execute('Test\Entity\Type', $identifier, $context);

        $this->assertEquals($expectedData, $actualData);
    }
}
