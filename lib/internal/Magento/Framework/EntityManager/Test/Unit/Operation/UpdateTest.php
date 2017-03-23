<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPool;

    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnection;

    /**
     * @var UpdateMain|\PHPUnit_Framework_MockObject_MockObject
     */
    private $updateMain;

    /**
     * @var Update
     */
    private $update;

    public function setUp()
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
     * @expectedException \Magento\Framework\Exception\AlreadyExistsException
     */
    public function testDuplicateExceptionProcessingOnExecute()
    {
        $metadata = $this->getMock(EntityMetadataInterface::class);
        $this->metadataPool->expects($this->any())->method('getMetadata')->willReturn($metadata);

        $connection = $this->getMock(AdapterInterface::class);
        $connection->expects($this->once())->method('rollback');
        $this->resourceConnection->expects($this->any())->method('getConnectionByName')->willReturn($connection);

        $this->updateMain->expects($this->once())->method('execute')->willThrowException(new DuplicateException());

        $entity = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->update->execute($entity);
    }
}
