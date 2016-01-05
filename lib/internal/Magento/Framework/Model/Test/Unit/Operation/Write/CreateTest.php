<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Operation\Write;

use Magento\Framework\Model\Entity\Action\CreateMain;
use Magento\Framework\Model\Entity\Action\CreateExtension;
use Magento\Framework\Model\Entity\Action\CreateRelation;
use Magento\Framework\Model\Operation\Write\Create;

/**
 * Class CreateTest
 */
class CreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CreateMain|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $createMainMock;

    /**
     * @var CreateExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $createExtensionMock;

    /**
     * @var CreateRelation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $createRelationMock;

    /**
     * @var Create
     */
    protected $create;

    protected function setUp()
    {
        $this->createMainMock = $this->getMockBuilder(CreateMain::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->createExtensionMock = $this->getMockBuilder(CreateExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->createRelationMock = $this->getMockBuilder(CreateRelation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->create = new Create(
            $this->createMainMock,
            $this->createExtensionMock,
            $this->createRelationMock
        );
    }

    public function testExecute()
    {
        $entityType = 'SomeNameSpace\SomeClassName';
        $entity = ['name' => 'test'];
        $entityWithMainCreate = array_merge($entity, ['main' => 'info']);
        $this->createMainMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entity
        )->willReturn($entityWithMainCreate);
        $entityWithExtensions = array_merge($entityWithMainCreate, ['ext' => 'extInfo']);
        $this->createExtensionMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entityWithMainCreate
        )->willReturn($entityWithExtensions);
        $entityWithRelations = array_merge($entityWithExtensions, ['relations' => 'info']);
        $this->createRelationMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entityWithExtensions
        )->willReturn($entityWithRelations);
        $this->assertEquals($entityWithRelations, $this->create->execute($entityType, $entity));
    }
}
