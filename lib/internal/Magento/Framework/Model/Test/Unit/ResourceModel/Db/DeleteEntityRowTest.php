<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

/**
 * Unit test for DeleteEntityRow class.
 */
class DeleteEntityRowTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\DeleteEntityRow
     */
    protected $subject;

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
        $this->connection = $this->getMockForAbstractClass(
            'Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            '',
            false,
            false,
            true,
            []
        );

        $metadata = $this->getMock(
            'Magento\Framework\EntityManager\EntityMetadata',
            [],
            [],
            '',
            false
        );

        $metadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');

        $metadata->expects($this->any())
            ->method('getEntityTable')
            ->willReturn('entity_table');

        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

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

        $this->subject = new \Magento\Framework\Model\ResourceModel\Db\DeleteEntityRow(
            $this->metadataPool
        );
    }

    public function testExecute()
    {
        $data = [
            'entity_id' => 1
        ];

        $this->connection->expects($this->once())
            ->method('delete')
            ->with('entity_table', ['entity_id = ?' => 1]);

        $this->subject->execute('Test\Entity\Type', $data);
    }
}
