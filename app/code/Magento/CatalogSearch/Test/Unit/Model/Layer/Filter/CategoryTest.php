<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Layer\Filter;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Category as CategoryDataProvider;
use Magento\Catalog\Model\Layer\Filter\DataProvider\CategoryFactory;
use Magento\Catalog\Model\Layer\Filter\Item;
use Magento\Catalog\Model\Layer\Filter\Item\DataBuilder;
use Magento\Catalog\Model\Layer\Filter\ItemFactory;
use Magento\CatalogSearch\Model\Layer\Filter\Category as CategoryFilter;
use Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\CatalogSearch\Model\Layer\Filter\Category
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoryTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var DataBuilder|MockObject
     */
    private $itemDataBuilder;

    /**
     * @var Category|MockObject
     */
    private $category;

    /**
     * @var Collection|MockObject
     */
    private $fulltextCollection;

    /**
     * @var Layer|MockObject
     */
    private $layer;

    /**
     * @var CategoryDataProvider|MockObject
     */
    private $dataProvider;

    /**
     * @var \Magento\CatalogSearch\Model\Layer\Filter\Category
     */
    private $target;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ItemFactory|MockObject
     */
    private $filterItemFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);

        $dataProviderFactory = $this->createPartialMock(
            CategoryFactory::class,
            ['create']
        );

        $this->dataProvider = $this->createPartialMock(
            CategoryDataProvider::class,
            ['setCategoryId', 'getCategory']
        );

        $dataProviderFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->dataProvider);

        $this->category = $this->createPartialMock(
            Category::class,
            ['getId', 'getChildrenCategories', 'getIsActive']
        );

        $this->dataProvider->method('getCategory')
            ->willReturn($this->category);

        $this->layer = $this->createPartialMock(
            Layer::class,
            ['getState', 'getProductCollection']
        );

        $this->fulltextCollection = $this->createPartialMock(
            Collection::class,
            ['addCategoryFilter', 'getFacetedData', 'getSize']
        );

        $this->layer->method('getProductCollection')
            ->willReturn($this->fulltextCollection);

        $this->itemDataBuilder = $this->createPartialMock(
            DataBuilder::class,
            ['addItemData', 'build']
        );

        $this->filterItemFactory = $this->createPartialMock(
            ItemFactory::class,
            ['create']
        );

        $filterItem = $this->createPartialMockWithReflection(
            Item::class,
            ['setFilter', 'setLabel', 'setValue', 'setCount']
        );
        $filterItem->method($this->anything())->willReturnSelf();
        $this->filterItemFactory->method('create')
            ->willReturn($filterItem);

        $escaper = $this->createPartialMock(
            Escaper::class,
            ['escapeHtml']
        );
        $escaper->method('escapeHtml')
            ->willReturnArgument(0);

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->target = $objectManagerHelper->getObject(
            CategoryFilter::class,
            [
                'categoryDataProviderFactory' => $dataProviderFactory,
                'layer' => $this->layer,
                'itemDataBuilder' => $this->itemDataBuilder,
                'filterItemFactory' => $this->filterItemFactory,
                'escaper' => $escaper
            ]
        );
    }

    /**
     * @param $requestValue
     * @param $idValue
     * @param $isIdUsed
     *
     * @return void
     */
    #[DataProvider('applyWithEmptyRequestDataProvider')]
    public function testApplyWithEmptyRequest($requestValue, $idValue): void
    {
        $requestField = 'test_request_var';
        $idField = 'id';

        $this->target->setRequestVar($requestField);

        $this->category->expects($this->once())
            ->method('getChildrenCategories')
            ->willReturn([]);

        $this->request->method('getParam')
            ->willReturnCallback(function ($field) use ($requestField, $idField, $requestValue, $idValue) {
                switch ($field) {
                    case $requestField:
                        return $requestValue;
                    case $idField:
                        return $idValue;
                }
            });

        $result = $this->target->apply($this->request);
        $this->assertSame($this->target, $result);
    }

    /**
     * @return array
     */
    public static function applyWithEmptyRequestDataProvider(): array
    {
        return [
            [
                'requestValue' => null,
                'idValue' => 0
            ],
            [
                'requestValue' => 0,
                'idValue' => false
            ],
            [
                'requestValue' => 0,
                'idValue' => null
            ]
        ];
    }

    /**
     * @return void
     */
    public function testApply(): void
    {
        $categoryId = 123;
        $requestVar = 'test_request_var';

        $this->target->setRequestVar($requestVar);
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(
                function ($field) use ($requestVar, $categoryId) {
                    $this->assertContains($field, [$requestVar, 'id']);
                    return $categoryId;
                }
            );

        $this->dataProvider->expects($this->once())
            ->method('setCategoryId')
            ->with($categoryId)->willReturnSelf();

        $this->category->expects($this->once())
            ->method('getId')
            ->willReturn($categoryId);

        $this->category->expects($this->once())
            ->method('getChildrenCategories')
            ->willReturn([]);

        $this->fulltextCollection->expects($this->once())
            ->method('addCategoryFilter')
            ->with($this->category)->willReturnSelf();

        $this->target->apply($this->request);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetItems(): void
    {
        $this->category->method('getIsActive')
            ->willReturn(true);

        $category1 = $this->createPartialMock(
            Category::class,
            ['getId', 'getName', 'getIsActive']
        );
        $category1->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(120);
        $category1->expects($this->once())
            ->method('getName')
            ->willReturn('Category 1');
        $category1->expects($this->once())
            ->method('getIsActive')
            ->willReturn(true);

        $category2 = $this->createPartialMock(
            Category::class,
            ['getId', 'getName', 'getIsActive']
        );
        $category2->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(5641);
        $category2->expects($this->once())
            ->method('getName')
            ->willReturn('Category 2');
        $category2->expects($this->once())
            ->method('getIsActive')
            ->willReturn(true);

        $category3 = $this->createPartialMock(
            Category::class,
            ['getId', 'getName', 'getIsActive']
        );
        $category3->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(777);
        $category3->expects($this->never())
            ->method('getName');
        $category3->expects($this->once())
            ->method('getIsActive')
            ->willReturn(true);

        $categories = [
            $category1,
            $category2,
            $category3
        ];
        $this->category->expects($this->once())
            ->method('getChildrenCategories')
            ->willReturn($categories);

        $facetedData = [
            120 => ['count' => 10],
            5641 => ['count' => 45],
            777 => ['count' => 80],
        ];

        $this->fulltextCollection->expects($this->once())
            ->method('getSize')
            ->willReturn(50);

        $this->fulltextCollection->expects($this->once())
            ->method('getFacetedData')
            ->with('category')
            ->willReturn($facetedData);

        $builtData = [
            [
                'label' => 'Category 1',
                'value' => 120,
                'count' => 10
            ],
            [
                'label' => 'Category 2',
                'value' => 5641,
                'count' => 45
            ]
        ];

        $this->itemDataBuilder
            ->method('addItemData')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) {
                if ($arg1 == 'Category 1' && $arg2 == 120 && $arg3 == 10) {
                    return $this->itemDataBuilder;
                } elseif ($arg1 == 'Category 2' && $arg2 == 5641 && $arg3 == 45) {
                    return $this->itemDataBuilder;
                }
            });
        $this->itemDataBuilder->expects($this->once())
            ->method('build')
            ->willReturn($builtData);

        $this->target->getItems();
    }
}
