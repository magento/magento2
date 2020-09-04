<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\CategoryFactory;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataCategoryHashMapTest extends TestCase
{
    /** @var CategoryRepository|MockObject */
    private $categoryRepository;

    /** @var CategoryResourceFactory|MockObject */
    private $categoryResourceFactory;

    /** @var Category|MockObject */
    private $categoryResource;

    /** @var DataCategoryHashMap|MockObject */
    private $model;

    protected function setUp(): void
    {
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->categoryResourceFactory = $this->createPartialMock(CategoryFactory::class, ['create']);
        $this->categoryResource = $this->createPartialMock(Category::class, ['getConnection', 'getEntityTable']);

        $this->categoryResourceFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->categoryResource);

        $this->model = (new ObjectManager($this))->getObject(
            DataCategoryHashMap::class,
            [
                'categoryRepository' => $this->categoryRepository,
                'categoryResourceFactory' => $this->categoryResourceFactory
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

        $categoryMock = $this->getMockBuilder(CategoryInterface::class)
            ->setMethods(['getResource'])
            ->getMockForAbstractClass();
        $connectionAdapterMock = $this->getMockForAbstractClass(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

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
