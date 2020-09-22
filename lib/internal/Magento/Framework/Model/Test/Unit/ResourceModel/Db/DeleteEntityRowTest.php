<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Test\Unit\ResourceModel\Db;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\DeleteEntityRow;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for DeleteEntityRow class.
 */
class DeleteEntityRowTest extends TestCase
{
    /**
     * Subject of testing.
     *
     * @var DeleteEntityRow
     */
    protected $subject;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connection;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    protected function setUp(): void
    {
        $this->connection = $this->getMockForAbstractClass(
            AdapterInterface::class,
            [],
            '',
            false,
            false,
            true,
            []
        );

        $metadata = $this->createMock(EntityMetadata::class);

        $metadata->expects($this->any())
            ->method('getLinkField')
            ->willReturn('entity_id');

        $metadata->expects($this->any())
            ->method('getEntityTable')
            ->willReturn('entity_table');

        $metadata->expects($this->any())
            ->method('getEntityConnection')
            ->willReturn($this->connection);

        $this->metadataPool = $this->createMock(MetadataPool::class);

        $this->metadataPool->expects($this->any())
            ->method('getMetadata')
            ->with('Test\Entity\Type')
            ->willReturn($metadata);

        $this->subject = new DeleteEntityRow(
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
