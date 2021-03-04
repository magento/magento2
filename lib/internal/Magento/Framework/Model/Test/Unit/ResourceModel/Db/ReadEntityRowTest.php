<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

/**
 * Unit test for ReadEntityRow class.
 */
class ReadEntityRowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\ReadEntityRow
     */
    protected $subject;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $metadataPool;

    protected function setUp(): void
    {
        $this->select = $this->createMock(\Magento\Framework\DB\Select::class);

        $this->connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
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

        $metadata = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);

        $metadata->expects($this->any())
            ->method('getEntityTable')
            ->willReturn('entity_table');

        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

        $metadata->expects($this->any())
            ->method('getIdentifierField')
            ->willReturn('identifier');

        $this->metadataPool = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with('Test\Entity\Type')
            ->willReturn($metadata);

        $this->subject = new \Magento\Framework\Model\ResourceModel\Db\ReadEntityRow(
            $this->metadataPool
        );
    }

    public function testExecute()
    {
        $identifier = '100000001';

        $context = ['store_id' => 1];
        $expectedData = ['entity_id' => 1];

        $this->select->expects($this->once())
            ->method('from')
            ->with(['t' => 'entity_table'])
            ->willReturnSelf();

        $this->select->expects($this->at(1))
            ->method('where')
            ->with('identifier = ?', $identifier)
            ->willReturnSelf();

        $this->select->expects($this->at(2))
            ->method('where')
            ->with('store_id = ?', 1)
            ->willReturnSelf();

        $this->connection->expects($this->once())
            ->method('fetchRow')
            ->willReturn($expectedData);

        $actualData = $this->subject->execute('Test\Entity\Type', $identifier, $context);

        $this->assertEquals($expectedData, $actualData);
    }
}
