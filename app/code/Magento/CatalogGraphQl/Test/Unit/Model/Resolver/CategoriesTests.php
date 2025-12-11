<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\CatalogGraphQl\Model\AttributesJoiner;
use Magento\CatalogGraphQl\Model\Category\Hydrator as CategoryHydrator;
use Magento\CatalogGraphQl\Model\Resolver\Categories;
use Magento\CatalogGraphQl\Model\Resolver\Product\ProductCategories;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CustomAttributesFlattener;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CategoriesTests extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private CollectionFactory $collectionFactory;

    /**
     * @var AttributesJoiner|MockObject
     */
    private AttributesJoiner $attributesJoiner;

    /**
     * @var CustomAttributesFlattener|MockObject
     */
    private CustomAttributesFlattener $customAttributesFlattener;

    /**
     * @var ValueFactory|MockObject
     */
    private ValueFactory $valueFactory;

    /**
     * @var CategoryHydrator|MockObject
     */
    private CategoryHydrator $categoryHydrator;

    /**
     * @var ProductCategories|MockObject
     */
    private ProductCategories $productCategories;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Categories
     */
    private Categories $categoriesResolver;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->attributesJoiner = $this->createMock(AttributesJoiner::class);
        $this->customAttributesFlattener = $this->createMock(CustomAttributesFlattener::class);
        $this->valueFactory = $this->createMock(ValueFactory::class);
        $this->categoryHydrator = $this->createMock(CategoryHydrator::class);
        $this->productCategories = $this->createMock(ProductCategories::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);

        $this->categoriesResolver = new Categories(
            $this->collectionFactory,
            $this->attributesJoiner,
            $this->customAttributesFlattener,
            $this->valueFactory,
            $this->categoryHydrator,
            $this->productCategories,
            $this->storeManager
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testResolveWithHiddenProduct(): void
    {
        $categoryId = 1;
        $field = $this->createMock(Field::class);
        $context = $this->createMock(ContextInterface::class);
        $info = $this->createMock(ResolveInfo::class);
        $info->path = [
            'orders',
            'customers'
        ];
        $product = $this->createMock(Product::class);
        $product->expects($this->once())
            ->method('getVisibility')
            ->willReturn(Visibility::VISIBILITY_NOT_VISIBLE);
        $product->expects($this->once())->method('getCategoryIds')->willReturn([$categoryId]);
        $value = [
            'model' => $product
        ];
        $args = [];
        $this->collectionFactory->expects($this->once())->method('create');

        $this->categoriesResolver->resolve($field, $context, $info, $value, $args);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testResolveWithVisibleProduct(): void
    {
        $categoryId = $storeId = $productId = 1;
        $field = $this->createMock(Field::class);
        $context = $this->createMock(ContextInterface::class);
        $info = $this->createMock(ResolveInfo::class);
        $info->path = [
            'orders',
            'customers'
        ];
        $product = $this->createMock(Product::class);
        $product->expects($this->once())
            ->method('getVisibility')
            ->willReturn(Visibility::VISIBILITY_IN_CATALOG);
        $product->expects($this->once())->method('getId')->willReturn([$productId]);
        $value = [
            'model' => $product
        ];
        $args = [];
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn($storeId);
        $this->storeManager->expects($this->once())->method('getStore')->willReturn($store);
        $this->productCategories->expects($this->once())
            ->method('getCategoryIdsByProduct')
            ->with($productId, $storeId)
            ->willReturn([$categoryId]);
        $this->collectionFactory->expects($this->once())->method('create');

        $this->categoriesResolver->resolve($field, $context, $info, $value, $args);
    }
}
