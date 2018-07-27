<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

/**
 * Unit test for ReadEntityRow class.
 */
class ReadEntityRowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\ReadEntityRow
     */
    protected $subject;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connection;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    protected function setUp()
    {
        $this->select = $this->getMock(
            'Magento\Framework\DB\Select',
            [],
            [],
            '',
            false
        );

        $this->connection = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Adapter\AdapterInterface',
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

        $metadata = $this->getMock(
            'Magento\Framework\EntityManager\EntityMetadata',
            [],
            [],
            '',
            false
        );

        $metadata->expects($this->any())
            ->method('getEntityTable')
            ->willReturn('entity_table');

        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

        $metadata->expects($this->any())
            ->method('getIdentifierField')
            ->willReturn('identifier');

        $this->metadataPool = $this->getMock(
            'Magento\Framework\EntityManager\MetadataPool',
            [],
            [],
            '',
            false
        );

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
