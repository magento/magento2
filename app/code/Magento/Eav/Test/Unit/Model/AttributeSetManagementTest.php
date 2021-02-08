<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model;

use Magento\Eav\Model\AttributeSetManagement;

class AttributeSetManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AttributeSetManagement
     */
    private $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $repositoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    private $eavConfigMock;

    protected function setUp(): void
    {
        $this->repositoryMock = $this->createMock(\Magento\Eav\Api\AttributeSetRepositoryInterface::class);
        $this->eavConfigMock = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['getEntityType']);

        $this->model = new \Magento\Eav\Model\AttributeSetManagement(
            $this->eavConfigMock,
            $this->repositoryMock
        );
    }

    public function testCreate()
    {
        $skeletonId = 1;
        $entityTypeCode = 'catalog_product';
        $entityTypeId = 4;
        $entityTypeMock = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $entityTypeMock->expects($this->any())->method('getId')->willReturn($entityTypeId);
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with($entityTypeCode)
            ->willReturn($entityTypeMock);
        $attributeSetMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Set::class,
            ['validate', 'getId', 'setEntityTypeId', 'initFromSkeleton']
        );
        $attributeSetMock->expects($this->once())->method('validate');
        $attributeSetMock->expects($this->once())->method('setEntityTypeId')->with($entityTypeId);
        $this->repositoryMock->expects($this->exactly(2))
            ->method('save')
            ->with($attributeSetMock)
            ->willReturn($attributeSetMock);
        $attributeSetMock->expects($this->once())->method('initFromSkeleton')->with($skeletonId);
        $this->assertEquals($attributeSetMock, $this->model->create($entityTypeCode, $attributeSetMock, $skeletonId));
    }

    /**
     */
    public function testCreateThrowsExceptionIfGivenAttributeSetAlreadyHasId()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Invalid value of "1" provided for the id field.');

        $skeletonId = 1;
        $entityTypeCode = 'catalog_product';
        $attributeSetMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Set::class,
            ['validate', 'getId', 'setEntityTypeId', 'initFromSkeleton']
        );
        $attributeSetMock->expects($this->any())->method('getId')->willReturn(1);
        $this->repositoryMock->expects($this->never())->method('save')->with($attributeSetMock);
        $attributeSetMock->expects($this->never())->method('initFromSkeleton')->with($skeletonId);
        $this->model->create($entityTypeCode, $attributeSetMock, $skeletonId);
    }

    /**
     */
    public function testCreateThrowsExceptionIfGivenSkeletonIdIsInvalid()
    {
        $this->expectException(\Magento\Framework\Exception\InputException::class);
        $this->expectExceptionMessage('Invalid value of "0" provided for the skeletonId field.');

        $skeletonId = 0;
        $entityTypeCode = 'catalog_product';
        $attributeSetMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Set::class,
            ['validate', 'getId', 'setEntityTypeId', 'initFromSkeleton']
        );
        $this->repositoryMock->expects($this->never())->method('save')->with($attributeSetMock);
        $attributeSetMock->expects($this->never())->method('initFromSkeleton')->with($skeletonId);
        $this->model->create($entityTypeCode, $attributeSetMock, $skeletonId);
    }

    /**
     */
    public function testCreateThrowsExceptionIfAttributeSetNotValid()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Wrong attribute properties');

        $entityTypeId = 4;
        $skeletonId = 5;
        $entityTypeCode = 'catalog_product';
        $attributeSetMock = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\Set::class,
            ['validate', 'getId', 'setEntityTypeId', 'initFromSkeleton']
        );

        $entityTypeMock = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $entityTypeMock->expects($this->any())->method('getId')->willReturn($entityTypeId);
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityType')
            ->with($entityTypeCode)
            ->willReturn($entityTypeMock);
        $attributeSetMock->expects($this->once())->method('setEntityTypeId')->with($entityTypeId);
        $attributeSetMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new \Exception('Wrong attribute properties'));

        $this->repositoryMock->expects($this->never())->method('save')->with($attributeSetMock);
        $attributeSetMock->expects($this->never())->method('initFromSkeleton')->with($skeletonId);
        $this->model->create($entityTypeCode, $attributeSetMock, $skeletonId);
    }
}
