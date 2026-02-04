<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit\Tab\Ajax;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Ajax\Serializer;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Serializer block
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Ajax\Serializer
 */
class SerializerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private Serializer $block;

    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @var MockObject&Json
     */
    private MockObject $serializerMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        // Prepare ObjectManager for helpers used by parent blocks
        $objects = [
            [JsonHelper::class, $this->createMock(JsonHelper::class)],
            [DirectoryHelper::class, $this->createMock(DirectoryHelper::class)]
        ];
        $this->objectManager->prepareObjectManager($objects);

        $this->serializerMock = $this->createMock(Json::class);

        $this->block = $this->objectManager->getObject(
            Serializer::class,
            [
                'context' => $this->createMock(Context::class),
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     * Test constructor sets serializer dependency
     *
     * @return void
     */
    public function testConstructorSetsSerializerDependency(): void
    {
        $this->assertInstanceOf(Serializer::class, $this->block);
    }

    /**
     * Test _construct sets correct template
     *
     * @return void
     */
    public function testConstructSetsCorrectTemplate(): void
    {
        $expectedTemplate = 'Magento_Catalog::catalog/product/edit/serializer.phtml';

        $this->assertSame($expectedTemplate, $this->block->getTemplate());
    }

    /**
     * Test getProductsJSON returns empty object when no products
     *
     * @return void
     */
    public function testGetProductsJsonReturnsEmptyObjectWhenNoProducts(): void
    {
        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $result = $this->block->getProductsJSON();

        $this->assertSame('{}', $result);
    }

    /**
     * Test getProductsJSON returns empty object when products is empty array
     *
     * @return void
     */
    public function testGetProductsJsonReturnsEmptyObjectWhenProductsIsEmptyArray(): void
    {
        $this->block->setProducts([]);

        $this->serializerMock->expects($this->never())
            ->method('serialize');

        $result = $this->block->getProductsJSON();

        $this->assertSame('{}', $result);
    }

    /**
     * Data provider for getProductsJSON scenarios
     *
     * @return array
     */
    public static function getProductsJsonDataProvider(): array
    {
        return [
            'single product without entity id flag' => [
                'isEntityId' => false,
                'products' => [
                    ['id' => 1, 'entityId' => 101, 'qty' => 5, 'position' => 1]
                ],
                'expectedResult' => [
                    1 => ['qty' => 5, 'position' => 1]
                ],
                'serializedOutput' => '{"1":{"qty":5,"position":1}}'
            ],
            'single product with entity id flag' => [
                'isEntityId' => true,
                'products' => [
                    ['id' => 1, 'entityId' => 101, 'qty' => 10, 'position' => 2]
                ],
                'expectedResult' => [
                    101 => ['qty' => 10, 'position' => 2]
                ],
                'serializedOutput' => '{"101":{"qty":10,"position":2}}'
            ],
            'multiple products without entity id flag' => [
                'isEntityId' => false,
                'products' => [
                    ['id' => 1, 'entityId' => 101, 'qty' => 5, 'position' => 1],
                    ['id' => 2, 'entityId' => 102, 'qty' => 3, 'position' => 2]
                ],
                'expectedResult' => [
                    1 => ['qty' => 5, 'position' => 1],
                    2 => ['qty' => 3, 'position' => 2]
                ],
                'serializedOutput' => '{"1":{"qty":5,"position":1},"2":{"qty":3,"position":2}}'
            ],
            'multiple products with entity id flag' => [
                'isEntityId' => true,
                'products' => [
                    ['id' => 1, 'entityId' => 101, 'qty' => 5, 'position' => 1],
                    ['id' => 2, 'entityId' => 102, 'qty' => 3, 'position' => 2]
                ],
                'expectedResult' => [
                    101 => ['qty' => 5, 'position' => 1],
                    102 => ['qty' => 3, 'position' => 2]
                ],
                'serializedOutput' => '{"101":{"qty":5,"position":1},"102":{"qty":3,"position":2}}'
            ]
        ];
    }

    /**
     * Test getProductsJSON returns serialized products data
     *
     * @dataProvider getProductsJsonDataProvider
     * @param bool $isEntityId
     * @param array $productsData
     * @param array $expectedResult
     * @param string $serializedOutput
     * @return void
     */
    public function testGetProductsJsonReturnsSerializedProductsData(
        bool $isEntityId,
        array $productsData,
        array $expectedResult,
        string $serializedOutput
    ): void {
        $productMocks = [];
        foreach ($productsData as $productData) {
            $productMock = $this->getMockBuilder(Product::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getId', 'getEntityId', 'toArray'])
                ->getMock();

            $productMock->method('getId')
                ->willReturn($productData['id']);
            $productMock->method('getEntityId')
                ->willReturn($productData['entityId']);
            $productMock->method('toArray')
                ->with(['qty', 'position'])
                ->willReturn(['qty' => $productData['qty'], 'position' => $productData['position']]);

            $productMocks[] = $productMock;
        }

        $this->block->setProducts($productMocks);
        $this->block->setIsEntityId($isEntityId);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($expectedResult)
            ->willReturn($serializedOutput);

        $result = $this->block->getProductsJSON();

        $this->assertSame($serializedOutput, $result);
    }

    /**
     * Test getProductsJSON uses getId when isEntityId is not set
     *
     * @return void
     */
    public function testGetProductsJsonUsesGetIdWhenIsEntityIdNotSet(): void
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getEntityId', 'toArray'])
            ->getMock();

        $productMock->expects($this->once())
            ->method('getId')
            ->willReturn(5);
        $productMock->expects($this->never())
            ->method('getEntityId');
        $productMock->method('toArray')
            ->with(['qty', 'position'])
            ->willReturn(['qty' => 1, 'position' => 0]);

        $this->block->setProducts([$productMock]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn('{"5":{"qty":1,"position":0}}');

        $this->block->getProductsJSON();
    }

    /**
     * Test getProductsJSON uses getEntityId when isEntityId is true
     *
     * @return void
     */
    public function testGetProductsJsonUsesGetEntityIdWhenIsEntityIdIsTrue(): void
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getEntityId', 'toArray'])
            ->getMock();

        $productMock->expects($this->never())
            ->method('getId');
        $productMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(100);
        $productMock->method('toArray')
            ->with(['qty', 'position'])
            ->willReturn(['qty' => 1, 'position' => 0]);

        $this->block->setProducts([$productMock]);
        $this->block->setIsEntityId(true);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn('{"100":{"qty":1,"position":0}}');

        $this->block->getProductsJSON();
    }

    /**
     * Test getProductsJSON calls toArray with qty and position fields
     *
     * @return void
     */
    public function testGetProductsJsonCallsToArrayWithQtyAndPositionFields(): void
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId', 'getEntityId', 'toArray'])
            ->getMock();

        $productMock->method('getId')->willReturn(1);
        $productMock->expects($this->once())
            ->method('toArray')
            ->with(['qty', 'position'])
            ->willReturn(['qty' => 10, 'position' => 5]);

        $this->block->setProducts([$productMock]);

        $this->serializerMock->method('serialize')
            ->willReturn('{}');

        $this->block->getProductsJSON();
    }
}
