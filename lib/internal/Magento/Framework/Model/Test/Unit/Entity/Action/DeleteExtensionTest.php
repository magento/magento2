<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Entity\Action;

use Magento\Framework\Model\Entity\Action\DeleteExtension;
use Magento\Framework\Model\Entity\EntityHydrator;
use Magento\Framework\Model\Entity\EntityMetadata;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\ExtensionPool;

class DeleteExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionPoolMock;

    /**
     * @var DeleteExtension
     */
    protected $deleteExtension;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionPoolMock = $this->getMockBuilder(ExtensionPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->deleteExtension = new DeleteExtension(
            $this->metadataPoolMock,
            $this->extensionPoolMock
        );
    }

    public function testExecute()
    {
        $entityType = 'Type';
        $entity = new \stdClass();
        $entityData = ['name' => 'test'];
        $entityHydrator = $this->getMockBuilder(EntityHydrator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $action = $this->getMockBuilder(\stdClass::class)
            ->disableOriginalConstructor()
            ->setMethods(['execute'])
            ->getMock();
        $entityMetadata = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock->expects($this->once())->method('getHydrator')->willReturn($entityHydrator);
        $this->metadataPoolMock->expects($this->once())->method('getMetadata')->willReturn($entityMetadata);
        $entityMetadata->expects($this->once())->method('getEavEntityType')->willReturn(true);
        $entityHydrator->expects($this->once())->method('extract')->with($entity)->willReturn([]);
        $this->extensionPoolMock->expects($this->once())
            ->method('getActions')
            ->with($entityType, 'delete')
            ->willReturn([$action]);
        $action->expects($this->once())->method('execute')->with($entityType, $entityData)->willReturn($entityData);
        $entityHydrator->expects($this->once())->method('hydrate')->with($entity, $entityData)->willReturn($entity);
        $this->assertEquals($entity, $this->deleteExtension->execute($entityType, $entity, $entityData));
    }
}
