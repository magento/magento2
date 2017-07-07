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
use Magento\Framework\EntityManager\Operation\Create;
use Magento\Framework\EntityManager\Operation\Create\CreateMain;
use Magento\Framework\EntityManager\Sequence\SequenceApplier;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CreateTest extends \PHPUnit_Framework_TestCase
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
     * @var CreateMain|\PHPUnit_Framework_MockObject_MockObject
     */
    private $createMain;

    /**
     * @var SequenceApplier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sequenceApplier;

    /**
     * @var Create
     */
    private $create;

    public function setUp()
    {
        $this->metadataPool = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourceConnection = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->createMain = $this->getMockBuilder(CreateMain::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->sequenceApplier = $this->getMockBuilder(SequenceApplier::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerHelper = new ObjectManager($this);
        $this->create = $objectManagerHelper->getObject(Create::class, [
            'metadataPool' => $this->metadataPool,
            'resourceConnection' => $this->resourceConnection,
            'createMain' => $this->createMain,
            'sequenceApplier' => $this->sequenceApplier,
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

        $this->createMain->expects($this->once())->method('execute')->willThrowException(new DuplicateException());

        $entity = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->create->execute($entity);
    }
}
