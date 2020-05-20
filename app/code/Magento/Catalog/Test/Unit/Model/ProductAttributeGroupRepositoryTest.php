<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model;

use Magento\Catalog\Model\Product\Attribute\GroupFactory;
use Magento\Catalog\Model\ProductAttributeGroupRepository;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductAttributeGroupRepositoryTest extends TestCase
{
    /**
     * @var ProductAttributeGroupRepository
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var MockObject
     */
    protected $groupFactoryMock;

    /**
     * @var MockObject
     */
    protected $groupResourceMock;

    protected function setUp(): void
    {
        $this->groupRepositoryMock = $this->getMockForAbstractClass(AttributeGroupRepositoryInterface::class);
        $this->groupFactoryMock = $this->createPartialMock(
            GroupFactory::class,
            ['create']
        );
        $this->groupResourceMock = $this->createPartialMock(
            Group::class,
            ['load']
        );

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ProductAttributeGroupRepository::class,
            [
                'groupRepository' => $this->groupRepositoryMock,
                'groupResource' => $this->groupResourceMock,
                'groupFactory' => $this->groupFactoryMock
            ]
        );
    }

    public function testSave()
    {
        $groupMock = $this->getMockForAbstractClass(AttributeGroupInterface::class);
        $expectedResult = $this->getMockForAbstractClass(AttributeGroupInterface::class);
        $this->groupRepositoryMock->expects($this->once())->method('save')->with($groupMock)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->model->save($groupMock));
    }

    public function testGetList()
    {
        $searchCriteriaMock = $this->getMockForAbstractClass(SearchCriteriaInterface::class);
        $expectedResult = $this->getMockForAbstractClass(AttributeGroupInterface::class);
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

    public function testGetThrowsExceptionIfGroupDoesNotExist()
    {
        $this->expectException('Magento\Framework\Exception\NoSuchEntityException');
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

    public function testDeleteThrowsExceptionIfGroupHasSystemAttributes()
    {
        $this->expectException('Magento\Framework\Exception\StateException');
        $this->expectExceptionMessage('The attribute group can\'t be deleted because it contains system attributes.');
        $groupMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Attribute\Group::class,
            ['hasSystemAttributes']
        );
        $groupMock->expects($this->once())->method('hasSystemAttributes')->willReturn(true);
        $this->model->delete($groupMock);
    }
}
