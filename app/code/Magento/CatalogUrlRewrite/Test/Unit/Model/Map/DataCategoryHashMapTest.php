<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Catalog\Model\ResourceModel\Category as CategoryResource;
use Magento\Framework\DB\Select;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class DataCategoryHashMapTest
 */
class DataCategoryHashMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var CategoryRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $categoryRepository;

    /** @var CategoryResource|\PHPUnit_Framework_MockObject_MockObject */
    private $categoryResource;

    /** @var DataCategoryHashMap|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    protected function setUp()
    {
        $this->categoryRepository = $this->getMock(CategoryRepository::class, [], [], '', false);
        $this->categoryResource = $this->getMock(
            CategoryResource::class,
            ['getConnection', 'getEntityTable'],
            [],
            '',
            false
        );

        $this->model = (new ObjectManager($this))->getObject(
            DataCategoryHashMap::class,
            [
                'categoryRepository' => $this->categoryRepository,
                'categoryResource' => $this->categoryResource
            ]
        );
    }

    /**
     * Tests getAllData, getData and resetData functionality
     */
    public function testGetAllData()
    {
        $categoryIds = ['1' => [1, 2, 3], '2' => [2, 3], '3' => 3];
        $categoryIdsOther = ['2' => [2, 3, 4]];

        $categoryMock = $this->getMock(CategoryInterface::class, [], [], '', false);
        $connectionAdapterMock = $this->getMock(AdapterInterface::class);
        $selectMock = $this->getMock(Select::class, [], [], '', false);

        $this->categoryRepository->expects($this->any())
            ->method('get')
            ->willReturn($categoryMock);
        $categoryMock->expects($this->any())
            ->method('getResource')
            ->willReturn($this->categoryResource);
        $this->categoryResource->expects($this->any())
            ->method('getConnection')
            ->willReturn($connectionAdapterMock);
        $this->categoryResource->expects($this->any())
            ->method('getEntityTable')
            ->willReturn('category_entity');
        $connectionAdapterMock->expects($this->any())
            ->method('select')
            ->willReturn($selectMock);
        $selectMock->expects($this->any())
            ->method('from')
            ->willReturnSelf();
        $selectMock->expects($this->any())
            ->method('where')
            ->willReturnSelf();
        $connectionAdapterMock->expects($this->any())
            ->method('fetchCol')
            ->willReturnOnConsecutiveCalls($categoryIds, $categoryIdsOther, $categoryIds);

        $this->assertEquals($categoryIds, $this->model->getAllData(1));
        $this->assertEquals($categoryIds[2], $this->model->getData(1, 2));
        $this->assertEquals($categoryIdsOther, $this->model->getAllData(2));
        $this->assertEquals($categoryIdsOther[2], $this->model->getData(2, 2));
        $this->model->resetData(1);
        $this->assertEquals($categoryIds[2], $this->model->getData(1, 2));
        $this->assertEquals($categoryIds, $this->model->getAllData(1));
    }
}
