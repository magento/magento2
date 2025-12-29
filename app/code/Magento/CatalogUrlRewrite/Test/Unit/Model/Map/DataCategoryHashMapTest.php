<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\Map;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\CategoryFactory;
use Magento\CatalogUrlRewrite\Model\Map\DataCategoryHashMap;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataCategoryHashMapTest extends TestCase
{
    use MockCreationTrait;

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

        $this->categoryResourceFactory->method('create')
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

        $categoryMock = $this->createPartialMockWithReflection(
            CategoryModel::class,
            ['getResource']
        );
        $connectionAdapterMock = $this->createMock(AdapterInterface::class);
        $selectMock = $this->createMock(Select::class);

        $this->categoryRepository->method('get')
            ->willReturn($categoryMock);
        $categoryMock->method('getResource')
            ->willReturn($this->categoryResource);
        $this->categoryResource->method('getConnection')
            ->willReturn($connectionAdapterMock);
        $this->categoryResource->method('getEntityTable')
            ->willReturn('category_entity');
        $connectionAdapterMock->method('select')
            ->willReturn($selectMock);
        $selectMock->method('from')
            ->willReturnSelf();
        $selectMock->method('where')
            ->willReturnSelf();
        
        $callCount = 0;
        $connectionAdapterMock->method('fetchCol')
            ->willReturnCallback(function () use (&$callCount, $categoryIds, $categoryIdsOther) {
                $callCount++;
                return match ($callCount) {
                    1 => $categoryIds,
                    2 => $categoryIdsOther,
                    3 => $categoryIds,
                    default => []
                };
            });

        $this->assertEquals($categoryIds, $this->model->getAllData(1));
        $this->assertEquals($categoryIds['2'], $this->model->getData(1, '2'));
        $this->assertEquals($categoryIdsOther, $this->model->getAllData(2));
        $this->assertEquals($categoryIdsOther['2'], $this->model->getData(2, '2'));
        $this->model->resetData(1);
        $this->assertEquals($categoryIds['2'], $this->model->getData(1, '2'));
        $this->assertEquals($categoryIds, $this->model->getAllData(1));
    }
}
