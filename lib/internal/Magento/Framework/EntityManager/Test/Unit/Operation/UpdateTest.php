<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Test\Unit\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\DuplicateException;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\Update;
use Magento\Framework\EntityManager\Operation\Update\UpdateMain;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class UpdateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MetadataPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $metadataPool;

    /**
     * @var ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    private $resourceConnection;

    /**
     * @var UpdateMain|\PHPUnit\Framework\MockObject\MockObject
     */
    private $updateMain;

    /**
     * @var Update
     */
    private $update;

    protected function setUp(): void
    {
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateMain = $this->getMockBuilder(UpdateMain::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->update = (new ObjectManager($this))->getObject(Update::class, [
            'metadataPool' => $this->metadataPool,
            'resourceConnection' => $this->resourceConnection,
            'updateMain' => $this->updateMain,
        ]);
    }

    /**
     */
    public function testDuplicateExceptionProcessingOnExecute()
    {
        $this->expectException(\Magento\Framework\Exception\AlreadyExistsException::class);

        $metadata = $this->getMockForAbstractClass(EntityMetadataInterface::class);
        $this->metadataPool->expects($this->any())->method('getMetadata')->willReturn($metadata);

        $connection = $this->getMockForAbstractClass(AdapterInterface::class);
        $connection->expects($this->once())->method('rollback');
        $this->resourceConnection->expects($this->any())->method('getConnectionByName')->willReturn($connection);

        $this->updateMain->expects($this->once())->method('execute')->willThrowException(new DuplicateException());

        $entity = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->update->execute($entity);
    }
}
