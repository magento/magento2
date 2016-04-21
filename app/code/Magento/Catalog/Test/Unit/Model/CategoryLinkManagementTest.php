<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model;


class CategoryLinkManagementTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\CategoryLinkManagement
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLinkFactoryMock;

    protected function setUp()
    {
        $this->categoryRepositoryMock = $this->getMock('\Magento\Catalog\Model\CategoryRepository', [], [], '', false);
        $productResource = $this->getMock('Magento\Catalog\Model\ResourceModel\Product', [], [], '', false);
        $categoryLinkRepository = $this->getMockBuilder('Magento\Catalog\Api\CategoryLinkRepositoryInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $indexerRegistry = $this->getMockBuilder('Magento\Framework\Indexer\IndexerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productLinkFactoryMock = $this->getMock(
            '\Magento\Catalog\Api\Data\CategoryProductLinkInterfaceFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->model = new \Magento\Catalog\Model\CategoryLinkManagement(
            $this->categoryRepositoryMock,
            $this->productLinkFactoryMock
        );

        $this->setProperties($this->model, [
            'productResource' => $productResource,
            'categoryLinkRepository' => $categoryLinkRepository,
            'productLinkFactory' => $this->productLinkFactoryMock,
            'indexerRegistry' => $indexerRegistry
        ]);
    }

    public function testGetAssignedProducts()
    {
        $categoryId = 42;
        $productId = 55;
        $position = 25;
        $productSku = 'testSku';
        $categoryProductLinkMock = $this->getMock('\Magento\Catalog\Api\Data\CategoryProductLinkInterface');
        $categoryMock = $this->getMock(
            '\Magento\Catalog\Model\Category',
            [],
            [],
            '',
            false
        );
        $productMock = $this->getMock('\Magento\Catalog\Model\Product', [], [], '', false);
        $productMock->expects($this->once())->method('getSku')->willReturn($productSku);
        $productMock->expects($this->once())->method('getData')->with('cat_index_position')->willReturn($position);
        $items = [$productId => $productMock];
        $productsMock = $this->getMock('Magento\Catalog\Model\ResourceModel\Product\Collection', [], [], '', false);
        $this->categoryRepositoryMock->expects($this->once())->method('get')->with($categoryId)
            ->willReturn($categoryMock);
        $categoryMock->expects($this->once())->method('getProductCollection')->willReturn($productsMock);
        $categoryMock->expects($this->once())->method('getId')->willReturn($categoryId);
        $productsMock->expects($this->once())->method('addFieldToSelect')->with('position')->willReturnSelf();
        $productsMock->expects($this->once())->method('getItems')->willReturn($items);
        $this->productLinkFactoryMock->expects($this->once())->method('create')->willReturn($categoryProductLinkMock);
        $categoryProductLinkMock->expects($this->once())
            ->method('setSku')
            ->with($productSku)
            ->willReturnSelf();
        $categoryProductLinkMock->expects($this->once())
            ->method('setPosition')
            ->with($position)
            ->willReturnSelf();
        $categoryProductLinkMock->expects($this->once())
            ->method('setCategoryId')
            ->with($categoryId)
            ->willReturnSelf();
        $this->assertEquals([$categoryProductLinkMock], $this->model->getAssignedProducts($categoryId));
    }

    /**
     * @param $object
     * @param array $properties
     */
    private function setProperties($object, $properties = [])
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        foreach ($properties as $key => $value) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($object, $value);
            }
        }
    }
}
