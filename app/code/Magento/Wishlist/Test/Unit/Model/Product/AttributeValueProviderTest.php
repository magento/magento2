<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Wishlist\Model\Product\AttributeValueProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeValueProviderTest
 *
 * PHPUnit test case for \Magento\Wishlist\Model\Product\AttributeValueProvider
 */
class AttributeValueProviderTest extends TestCase
{
    /**
     * @var AttributeValueProvider|MockObject
     */
    private $attributeValueProvider;

    /**
     * @var CollectionFactory|MockObject
     */
    private $productCollectionFactoryMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->productCollectionFactoryMock = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->attributeValueProvider = new AttributeValueProvider(
            $this->productCollectionFactoryMock
        );
    }

    /**
     * Get attribute text when the flat table is disabled
     *
     * @param int $productId
     * @param string $attributeCode
     * @param string $attributeText
     * @return void
     * @dataProvider attributeDataProvider
     */
    public function testGetAttributeTextWhenFlatIsDisabled(int $productId, string $attributeCode, string $attributeText)
    {
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();

        $this->productMock->expects($this->any())
            ->method('getData')
            ->with($attributeCode)
            ->willReturn($attributeText);

        $productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'addIdFilter', 'addStoreFilter', 'addAttributeToSelect', 'isEnabledFlat', 'getFirstItem'
            ])->getMock();

        $productCollection->expects($this->any())
            ->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('isEnabledFlat')
            ->willReturn(false);
        $productCollection->expects($this->any())
            ->method('getFirstItem')
            ->willReturn($this->productMock);

        $this->productCollectionFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($productCollection);

        $actual = $this->attributeValueProvider->getRawAttributeValue($productId, $attributeCode);

        $this->assertEquals($attributeText, $actual);
    }

    /**
     * Get attribute text when the flat table is enabled
     *
     * @dataProvider attributeDataProvider
     * @param int $productId
     * @param string $attributeCode
     * @param string $attributeText
     * @return void
     */
    public function testGetAttributeTextWhenFlatIsEnabled(int $productId, string $attributeCode, string $attributeText)
    {
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $this->connectionMock->expects($this->any())
            ->method('fetchRow')
            ->willReturn([
                $attributeCode => $attributeText
            ]);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();
        $this->productMock->expects($this->any())
            ->method('getData')
            ->with($attributeCode)
            ->willReturn($attributeText);

        $productCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'addIdFilter', 'addStoreFilter', 'addAttributeToSelect', 'isEnabledFlat', 'getConnection'
            ])->getMock();

        $productCollection->expects($this->any())
            ->method('addIdFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addStoreFilter')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('addAttributeToSelect')
            ->willReturnSelf();
        $productCollection->expects($this->any())
            ->method('isEnabledFlat')
            ->willReturn(true);
        $productCollection->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);

        $this->productCollectionFactoryMock->expects($this->atLeastOnce())
            ->method('create')
            ->willReturn($productCollection);

        $actual = $this->attributeValueProvider->getRawAttributeValue($productId, $attributeCode);

        $this->assertEquals($attributeText, $actual);
    }

    /**
     * @return array
     */
    public static function attributeDataProvider(): array
    {
        return [
            [1, 'attribute_code', 'Attribute Text']
        ];
    }
}
