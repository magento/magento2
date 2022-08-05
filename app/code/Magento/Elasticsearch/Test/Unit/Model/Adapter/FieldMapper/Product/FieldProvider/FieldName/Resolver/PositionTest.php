<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver\Position;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD)
 */
class PositionTest extends TestCase
{
    /**
     * @var Position
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
        $this->storeManager = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMockForAbstractClass();
        $this->coreRegistry = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->setMethods(['registry'])
            ->getMock();

        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            Position::class,
            [
                'storeManager' => $this->storeManager,
                'coreRegistry' => $this->coreRegistry,
            ]
        );
    }

    /**
     * @dataProvider getFieldNameProvider
     * @param $attributeCode
     * @param $context
     * @param $fromRegistry
     * @param $expected
     * @return void
     */
    public function testGetFieldName($attributeCode, $context, $fromRegistry, $expected)
    {
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode'])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $store = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRootCategoryId'])
            ->getMockForAbstractClass();
        $store->expects($this->any())
            ->method('getRootCategoryId')
            ->willReturn(2);
        $this->storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($store);
        $category = null;
        if ($fromRegistry) {
            $category = $this->getMockBuilder(CategoryInterface::class)
                ->disableOriginalConstructor()
                ->setMethods(['getId'])
                ->getMockForAbstractClass();
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
    public function getFieldNameProvider()
    {
        return [
            ['position', [], true, 'position_category_1'],
            ['position', [], false, 'position_category_2'],
            ['position', ['categoryId' => 2], false, 'position_category_2'],
            ['price', ['categoryId' => 2], false, ''],
        ];
    }
}
