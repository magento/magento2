<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

class ProductAttributeGroupRepositoryTest extends \PHPUnit_Framework_TestCase
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
        $this->groupRepositoryMock = $this->getMock(\Magento\Eav\Api\AttributeGroupRepositoryInterface::class);
        $this->groupFactoryMock = $this->getMock(
            \Magento\Catalog\Model\Product\Attribute\GroupFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->groupResourceMock = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group::class,
            ['load', '__wakeup'],
            [],
            '',
            false
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
        $groupMock = $this->getMock(\Magento\Eav\Api\Data\AttributeGroupInterface::class);
        $expectedResult = $this->getMock(\Magento\Eav\Api\Data\AttributeGroupInterface::class);
        $this->groupRepositoryMock->expects($this->once())->method('save')->with($groupMock)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->model->save($groupMock));
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMock(\Magento\Framework\Api\SearchCriteriaInterface::class);
        $expectedResult = $this->getMock(\Magento\Eav\Api\Data\AttributeGroupInterface::class);
        $this->groupRepositoryMock->expects($this->once())->method('getList')->with($searchCriteriaMock)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->model->getList($searchCriteriaMock));
    }

    public function testGet()
    {
        $groupId = 42;
        $groupMock = $this->getMock(\Magento\Catalog\Model\Product\Attribute\Group::class, [], [], '', false);
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
        $groupMock = $this->getMock(\Magento\Catalog\Model\Product\Attribute\Group::class, [], [], '', false);
        $this->groupFactoryMock->expects($this->once())->method('create')->willReturn($groupMock);
        $this->groupResourceMock->expects($this->once())->method('load')->with($groupMock, $groupId)->willReturnSelf();
        $groupMock->expects($this->once())->method('getId')->willReturn(null);
        $this->model->get($groupId);
    }

    public function testDeleteById()
    {
        $groupId = 42;
        $groupMock = $this->getMock(
            \Magento\Catalog\Model\Product\Attribute\Group::class,
            ['hasSystemAttributes', 'getId'],
            [],
            '',
            false
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
        $groupMock = $this->getMock(
            \Magento\Catalog\Model\Product\Attribute\Group::class,
            ['hasSystemAttributes'],
            [],
            '',
            false
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
        $groupMock = $this->getMock(
            \Magento\Catalog\Model\Product\Attribute\Group::class,
            ['hasSystemAttributes'],
            [],
            '',
            false
        );
        $groupMock->expects($this->once())->method('hasSystemAttributes')->willReturn(true);
        $this->model->delete($groupMock);
    }
}
