<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\CategoryName;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @SuppressWarnings(PHPMD)
 */
class CategoryNameTest extends TestCase
{
    /**
     * @var CategoryName
     */
    private $resolver;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManager = $this->createPartialMock(StoreManager::class, ['getStore']);
        $this->coreRegistry = $this->createPartialMock(Registry::class, ['registry']);

        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            CategoryName::class,
            [
                'storeManager' => $this->storeManager,
                'coreRegistry' => $this->coreRegistry,
            ]
        );
    }

    /**
     * @param $attributeCode
     * @param $context
     * @param $fromRegistry
     * @param $expected
     * @return void
     */
    #[DataProvider('getFieldNameProvider')]
    public function testGetFieldName($attributeCode, $context, $fromRegistry, $expected)
    {
        $attributeMock = $this->createPartialMock(AttributeAdapter::class, ['getAttributeCode']);
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $store = $this->createPartialMock(Store::class, ['getRootCategoryId']);
        $store->expects($this->any())
            ->method('getRootCategoryId')
            ->willReturn(2);
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $category = null;
        if ($fromRegistry) {
            $category = $this->createMock(CategoryInterface::class);
            $category->expects($this->any())
                ->method('getId')
                ->willReturn(1);
        }
        $this->coreRegistry->expects($this->any())
            ->method('registry')
            ->willReturn($category);

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldName($attributeMock, $context)
        );
    }

    /**
     * @return array
     */
    public static function getFieldNameProvider()
    {
        return [
            ['category_name', [], true, 'name_category_1'],
            ['category_name', [], false, 'name_category_2'],
            ['category_name', ['categoryId' => 3], false, 'name_category_3'],
            ['price', ['categoryId' => 3], false, ''],
        ];
    }
}
