<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Operation\Write;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\Action\UpdateMain;
use Magento\Framework\Model\Entity\Action\UpdateExtension;
use Magento\Framework\Model\Entity\Action\UpdateRelation;
use Magento\Framework\Model\Entity\EntityMetadata;
use Magento\Framework\Model\Operation\Write\Update;

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateMain|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateMainMock;

    /**
     * @var UpdateExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateExtensionMock;

    /**
     * @var UpdateRelation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $updateRelationMock;

    /**
     * @var Update
     */
    protected $update;

    protected function setUp()
    {
        $this->updateMainMock = $this->getMockBuilder(UpdateMain::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateExtensionMock = $this->getMockBuilder(UpdateExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->updateRelationMock = $this->getMockBuilder(UpdateRelation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->update = new Update(
            $this->updateMainMock,
            $this->updateExtensionMock,
            $this->updateRelationMock
        );
    }

    public function testExecute()
    {
        $entityType = 'SomeNameSpace\SomeClassName';
        $entity = ['name' => 'test'];
        $entityWithMainCreate = array_merge($entity, ['main' => 'info']);
        $this->updateMainMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entity
        )->willReturn($entityWithMainCreate);
        $entityWithExtensions = array_merge($entityWithMainCreate, ['ext' => 'extInfo']);
        $this->updateExtensionMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entityWithMainCreate
        )->willReturn($entityWithExtensions);
        $entityWithRelations = array_merge($entityWithExtensions, ['relations' => 'info']);
        $this->updateRelationMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entityWithExtensions
        )->willReturn($entityWithRelations);
        $this->assertEquals($entityWithRelations, $this->update->execute($entityType, $entity));
    }
}
