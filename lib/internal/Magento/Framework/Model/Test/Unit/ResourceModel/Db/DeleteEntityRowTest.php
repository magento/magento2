<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

/**
 * Unit test for DeleteEntityRow class.
 */
class DeleteEntityRowTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Subject of testing.
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\DeleteEntityRow
     */
    protected $subject;

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
        $this->connection = $this->getMockForAbstractClass(
            \Magento\Framework\DB\Adapter\AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        $metadata = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);

        $metadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');

        $metadata->expects($this->any())
            ->method('getEntityTable')
            ->willReturn('entity_table');

        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

        $this->metadataPool = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);

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
