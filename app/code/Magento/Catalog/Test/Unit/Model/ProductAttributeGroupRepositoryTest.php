<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

class ProductAttributeGroupRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductAttributeGroupRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupResourceMock;

    protected function setUp()
    {
        $this->groupRepositoryMock = $this->createMock(\Magento\Eav\Api\AttributeGroupRepositoryInterface::class);
        $this->groupFactoryMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Attribute\GroupFactory::class,
            ['create']
        );
        $this->groupResourceMock = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group::class,
            ['load', '__wakeup']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\ProductAttributeGroupRepository::class,
            [
                'groupRepository' => $this->groupRepositoryMock,
                'groupResource' => $this->groupResourceMock,
                'groupFactory' => $this->groupFactoryMock
            ]
        );
    }

    public function testSave()
    {
        $groupMock = $this->createMock(\Magento\Eav\Api\Data\AttributeGroupInterface::class);
        $expectedResult = $this->createMock(\Magento\Eav\Api\Data\AttributeGroupInterface::class);
        $this->groupRepositoryMock->expects($this->once())->method('save')->with($groupMock)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->model->save($groupMock));
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $expectedResult = $this->createMock(\Magento\Eav\Api\Data\AttributeGroupInterface::class);
        $this->groupRepositoryMock->expects($this->once())->method('getList')->with($searchCriteriaMock)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->model->getList($searchCriteriaMock));
    }

    public function testGet()
    {
        $groupId = 42;
        $groupMock = $this->createMock(\Magento\Catalog\Model\Product\Attribute\Group::class);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId)->willReturnSelf();
        $groupMock->expects($this->once())->method('getId')->willReturn($groupId);
        $this->assertEquals($groupMock, $this->model->get($groupId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     */
    public function testGetThrowsExceptionIfGroupDoesNotExist()
    {
        $groupId = 42;
        $groupMock = $this->createMock(\Magento\Catalog\Model\Product\Attribute\Group::class);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId)->willReturnSelf();
        $groupMock->expects($this->once())->method('getId')->willReturn(null);
        $this->model->get($groupId);
    }

    public function testDeleteById()
    {
        $groupId = 42;
        $groupMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Attribute\Group::class,
            ['hasSystemAttributes', 'getId']
        );
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId)->willReturnSelf();

        $groupMock->expects($this->once())->method('getId')->willReturn($groupId);
        $groupMock->expects($this->once())->method('hasSystemAttributes')->willReturn(false);

        $this->groupRepositoryMock->expects($this->once())->method('delete')->with($groupMock)->willReturn(true);
        $this->assertTrue($this->model->deleteById($groupId));
    }

    public function testDelete()
    {
        $groupMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Attribute\Group::class,
            ['hasSystemAttributes']
        );
        $groupMock->expects($this->once())->method('hasSystemAttributes')->willReturn(false);
        $this->groupRepositoryMock->expects($this->once())->method('delete')->with($groupMock)->willReturn(true);
        $this->assertTrue($this->model->delete($groupMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Attribute group that contains system attributes can not be deleted
     */
    public function testDeleteThrowsExceptionIfGroupHasSystemAttributes()
    {
        $groupMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Attribute\Group::class,
            ['hasSystemAttributes']
        );
        $groupMock->expects($this->once())->method('hasSystemAttributes')->willReturn(true);
        $this->model->delete($groupMock);
    }
}
