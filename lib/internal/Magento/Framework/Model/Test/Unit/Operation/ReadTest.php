<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Test\Unit\Operation;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\Entity\Action\ReadMain;
use Magento\Framework\Model\Entity\Action\ReadExtension;
use Magento\Framework\Model\Entity\Action\ReadRelation;
use Magento\Framework\Model\Operation\Read;
use Magento\Framework\Model\Entity\EntityMetadata;

/**
 * Class ReadTest
 */
class ReadTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPoolMock;

    /**
     * @var EntityMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataMock;

    /**
     * @var ReadMain|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readMainMock;

    /**
     * @var ReadExtension|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readExtensionMock;

    /**
     * @var ReadRelation|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readRelationMock;

    /**
     * @var Read
     */
    protected $read;

    protected function setUp()
    {
        $this->metadataPoolMock = $this->getMockBuilder(MetadataPool::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metadataMock = $this->getMockBuilder(EntityMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readMainMock = $this->getMockBuilder(ReadMain::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readExtensionMock = $this->getMockBuilder(ReadExtension::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->readRelationMock = $this->getMockBuilder(ReadRelation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->read = new Read(
            $this->metadataPoolMock,
            $this->readMainMock,
            $this->readExtensionMock,
            $this->readRelationMock
        );
    }

    /**
     * @param string $entityType
     * @param array $entity
     * @param string $identifier
     * @param string $linkField
     * @dataProvider executeParameters
     */
    public function testExecute($entityType, $entity, $identifier, $linkField)
    {
        $this->metadataPoolMock->expects($this->once())->method('getMetadata')->with($entityType)->willReturn(
            $this->metadataMock
        );
        $entityWithMainRead = array_merge($entity, ['main_read' => 'some info']);
        $this->readMainMock->expects($this->once())->method('execute')->with(
            $entityType,
            $entity,
            $identifier
        )->willReturn($entityWithMainRead);
        $this->metadataMock->expects($this->once())->method('getLinkField')->willReturn($linkField);
        $entityWithExtensionAndRelation = $entityWithMainRead;
        if (isset($entity[$linkField])) {
            $entityWithExtension = array_merge($entityWithMainRead, ['ext' => 'extParameter']);
            $this->readExtensionMock->expects($this->once())->method('execute')->with(
                $entityType,
                $entityWithMainRead
            )->willReturn($entityWithExtension);
            $entityWithExtensionAndRelation = array_merge($entityWithExtension, ['relation' => 'some_relation']);
            $this->readRelationMock->expects($this->once())->method('execute')->with(
                $entityType,
                $entityWithExtension
            )->willReturn($entityWithExtensionAndRelation);
        }

        $this->assertEquals(
            $entityWithExtensionAndRelation,
            $this->read->execute(
                $entityType,
                $entity,
                $identifier
            )
        );
    }

    /**
     * @return array
     */
    public function executeParameters()
    {
        return [
            ['SomeNameSpace\SomeEntityClass', ['id' => 1, 'name' => 'some name'], 'id', 'id'],
            ['SomeNameSpace\SomeEntityClass', ['id' => 1, 'name' => 'some name'], 'id', 'nonExistingLinkField'],
        ];
    }
}
