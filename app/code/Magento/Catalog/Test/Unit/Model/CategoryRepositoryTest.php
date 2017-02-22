<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model;

class CategoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryFactoryMock;

    /**
     * @var  \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    protected function setUp()
    {
        $this->categoryFactoryMock = $this->getMock(
            '\Magento\Catalog\Model\CategoryFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->categoryResourceMock =
            $this->getMock('\Magento\Catalog\Model\ResourceModel\Category', [], [], '', false);
        $this->storeManagerMock = $this->getMock('\Magento\Store\Model\StoreManagerInterface');

        $this->model = new \Magento\Catalog\Model\CategoryRepository(
            $this->categoryFactoryMock,
            $this->categoryResourceMock,
            $this->storeManagerMock
        );
    }

    public function testGet()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false);
        $categoryMock->expects(
            $this->once()
        )->method('getId')->willReturn(
            $categoryId
        );
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects(
            $this->once()
        )->method('load')->with(
            $categoryId
        );

        $this->assertEquals($categoryMock, $this->model->get($categoryId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 5
     */
    public function testGetWhenCategoryDoesNotExist()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false);
        $categoryMock->expects(
            $this->once()
        )->method('getId')->willReturn(null);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects(
            $this->once()
        )->method('load')->with(
            $categoryId
        );

        $this->assertEquals($categoryMock, $this->model->get($categoryId));
    }

    public function testUpdateExistingCategory()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false, true, true);
        $categoryMock->expects(
            $this->atLeastOnce()
        )->method('getId')->willReturn($categoryId);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects($this->atLeastOnce())->method('toFlatArray')->willReturn(['image' => []]);
        $categoryMock->expects($this->once())->method('validate')->willReturn(true);
        $categoryMock->expects($this->once())->method('getParentId')->willReturn(3);
        $categoryMock->expects($this->once())->method('getPath')->willReturn('path');
        $categoryMock->expects($this->once())->method('getIsActive')->willReturn(true);
        $this->categoryResourceMock->expects($this->once())
            ->method('save')
            ->willReturn('\Magento\Framework\DataObject');
        $this->assertEquals($categoryMock, $this->model->save($categoryMock));
    }

    public function testCreateNewCategory()
    {
        $categoryId = null;
        $parentCategoryId = 15;
        $newCategoryId = 25;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false, true, true);
        $parentCategoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false, true, true);
        $categoryMock->expects($this->any())->method('getId')
            ->will($this->onConsecutiveCalls($categoryId, $newCategoryId));
        $categoryMock->expects($this->never())->method('getIsActive');
        $this->categoryFactoryMock->expects($this->once())->method('create')->willReturn($parentCategoryMock);
        $parentCategoryMock->expects($this->atLeastOnce())->method('getId')->willReturn($parentCategoryId);

        $categoryMock->expects($this->once())->method('getParentId')->willReturn($parentCategoryId);
        $parentCategoryMock->expects($this->once())->method('getPath')->willReturn('path');
        $categoryMock->expects($this->once())->method('validate')->willReturn(true);
        $categoryMock->expects($this->once())->method('getParentId')->willReturn(3);
        $this->categoryResourceMock->expects($this->once())
            ->method('save')
            ->willReturn('\Magento\Framework\DataObject');
        $this->assertEquals($categoryMock, $this->model->save($categoryMock));
    }

    /**
     * @expectedException \Magento\Framework\Exception\CouldNotSaveException
     * @expectedExceptionMessage Could not save category
     */
    public function testSaveWithException()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false, true, true);
        $categoryMock->expects(
            $this->atLeastOnce()
        )->method('getId')->willReturn($categoryId);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects($this->once())->method('validate')->willReturn(false);
        $categoryMock->expects($this->once())->method('getParentId')->willReturn(3);
        $this->model->save($categoryMock);
    }

    /**
     * @dataProvider saveWithValidateCategoryExceptionDataProvider
     */
    public function testSaveWithValidateCategoryException($error, $expectedException, $expectedExceptionMessage)
    {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $categoryId = 5;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false);
        $objectMock = $this->getMock('\Magento\Framework\DataObject', ['getFrontend', 'getLabel'], [], '', false);
        $categoryMock->expects(
            $this->atLeastOnce()
        )->method('getId')->willReturn($categoryId);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $objectMock->expects($this->any())->method('getFrontend')->willReturn($objectMock);
        $objectMock->expects($this->any())->method('getLabel')->willReturn('ValidateCategoryTest');
        $categoryMock->expects($this->once())->method('getParentId')->willReturn(3);
        $categoryMock->expects($this->once())->method('validate')->willReturn([42 => $error]);
        $this->categoryResourceMock->expects($this->any())->method('getAttribute')->with(42)->willReturn($objectMock);
        $categoryMock->expects($this->never())->method('unsetData');
        $this->model->save($categoryMock);
    }

    public function saveWithValidateCategoryExceptionDataProvider()
    {
        return [
            [
                true,
                '\Magento\Framework\Exception\CouldNotSaveException',
                'Could not save category: Attribute "ValidateCategoryTest" is required.',
            ], [
                'Something went wrong',
                '\magento\Framework\Exception\CouldNotSaveException',
                'Could not save category: Something went wrong'
            ]
        ];
    }

    public function testDelete()
    {
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false, true, true);
        $this->assertTrue($this->model->delete($categoryMock));
    }

    /**
     * @throws \Magento\Framework\Exception\StateException
     * @expectedException \Magento\Framework\Exception\StateException
     * @expectedExceptionMessage Cannot delete category with id
     */
    public function testDeleteWithException()
    {
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false, true, true);
        $this->categoryResourceMock->expects($this->once())->method('delete')->willThrowException(new \Exception());
        $this->model->delete($categoryMock);
    }

    public function testDeleteByIdentifier()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false);
        $categoryMock->expects(
            $this->any()
        )->method('getId')->willReturn(
            $categoryId
        );
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects(
            $this->once()
        )->method('load')->with(
            $categoryId
        );
        $this->assertTrue($this->model->deleteByIdentifier($categoryId));
    }

    /**
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage No such entity with id = 5
     */
    public function testDeleteByIdentifierWithException()
    {
        $categoryId = 5;
        $categoryMock = $this->getMock('\Magento\Catalog\Model\Category', [], [], '', false);
        $categoryMock->expects(
            $this->once()
        )->method('getId')->willReturn(null);
        $this->categoryFactoryMock->expects(
            $this->once()
        )->method('create')->willReturn(
            $categoryMock
        );
        $categoryMock->expects(
            $this->once()
        )->method('load')->with(
            $categoryId
        );
        $this->model->deleteByIdentifier($categoryId);
    }
}
