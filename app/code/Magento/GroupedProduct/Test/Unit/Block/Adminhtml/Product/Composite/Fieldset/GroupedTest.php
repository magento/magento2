<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Block\Adminhtml\Product\Composite\Fieldset;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::class)]
class GroupedTest extends TestCase
{
    /**
     * @var Grouped
     */
    protected $block;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $pricingHelperMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    protected function setUp(): void
    {
        $this->registryMock = $this->createMock(Registry::class);
        $this->productMock = $this->createMock(Product::class);
        $this->pricingHelperMock = $this->createMock(Data::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);

        $customerMock = $this->createMock(CustomerInterface::class);
        $customerMock->method('getId')->willReturn(1);

        $objectHelper = new ObjectManager($this);
        $this->block = $objectHelper->getObject(
            Grouped::class,
            [
                'registry' => $this->registryMock,
                'storeManager' => $this->storeManagerMock,
                'pricingHelper' => $this->pricingHelperMock,
                'data' => ['product' => $this->productMock]
            ]
        );
    }

    public function testGetProductPositive()
    {
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $storeMock = $this->createMock(Store::class);

        $this->productMock->method('getTypeInstance')->willReturn($instanceMock);

        $instanceMock->expects($this->once())->method('getStoreFilter')->willReturn($storeMock);

        $instanceMock->expects($this->never())->method('setStoreFilter');

        $this->assertEquals($this->productMock, $this->block->getProduct());
    }

    public function testGetProductNegative()
    {
        $storeId = 2;
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $storeMock = $this->createMock(Store::class);

        $this->productMock->method('getTypeInstance')->willReturn($instanceMock);

        $instanceMock->expects(
            $this->once()
        )->method(
            'getStoreFilter'
        )->with(
            $this->productMock
        )->willReturn(
            null
        );

        $this->productMock->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $this->storeManagerMock->expects(
            $this->any()
        )->method(
            'getStore'
        )->with(
            $storeId
        )->willReturn(
            $storeMock
        );

        $instanceMock->expects($this->once())->method('setStoreFilter')->with($storeMock, $this->productMock);

        $this->assertEquals($this->productMock, $this->block->getProduct());
    }

    public function testGetAssociatedProducts()
    {
        $storeId = 2;

        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->productMock->method('getTypeInstance')->willReturn($instanceMock);

        $associatedProduct = clone $this->productMock;

        $associatedProduct->expects($this->once())->method('setStoreId')->with($storeId);

        $instanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            [$associatedProduct]
        );

        $this->productMock->method('getStoreId')->willReturn($storeId);

        $this->assertEquals([$associatedProduct], $this->block->getAssociatedProducts());
    }

    public function testSetPreconfiguredValue()
    {
        $storeId = 2;

        $objectMock = new \Magento\Framework\DataObject\Test\Unit\Helper\DataObjectTestHelper();
        $objectMock->setSuperGroup([]);
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->productMock->expects(
            $this->once()
        )->method(
            'getPreconfiguredValues'
        )->willReturn(
            $objectMock
        );

        $this->productMock->method('getTypeInstance')->willReturn($instanceMock);

        $associatedProduct = clone $this->productMock;

        $associatedProduct->expects($this->once())->method('setStoreId')->with($storeId);

        $instanceMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            [$associatedProduct]
        );

        $this->productMock->method('getStoreId')->willReturn($storeId);

        $this->assertEquals($this->block, $this->block->setPreconfiguredValue());
    }

    public function testGetCanShowProductPrice()
    {
        $this->assertTrue($this->block->getCanShowProductPrice($this->productMock));
    }

    public function testGetIsLastFieldsetPositive()
    {
        $this->block->setData('is_last_fieldset', true);

        $this->productMock->expects($this->never())->method('getOptions');

        $this->assertTrue($this->block->getIsLastFieldset());
    }

    /**
     * @param array|bool $options
     * @param bool $expectedResult
     */
    #[DataProvider('getIsLastFieldsetDataProvider')]
    public function testGetIsLastFieldsetNegative($options, $expectedResult)
    {
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->block->setData('is_last_fieldset', false);

        $this->productMock->expects($this->once())->method('getOptions')->willReturn($options);

        $this->productMock->method('getTypeInstance')->willReturn($instanceMock);

        $instanceMock->expects($this->once())->method('getStoreFilter')->willReturn(true);

        $this->assertEquals($expectedResult, $this->block->getIsLastFieldset());
    }

    /**
     * Data provider for testGetIsLastFieldsetNegative
     *
     * @return array
     */
    public static function getIsLastFieldsetDataProvider()
    {
        return [
            'case1' => ['options' => false, 'expectedResult' => true],
            'case2' => ['options' => ['option'], 'expectedResult' => false]
        ];
    }

    public function testGetCurrencyPrice()
    {
        $storeId = 2;
        $price = 1.22;
        $expectedPrice = 1;

        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->productMock->method('getTypeInstance')->willReturn($instanceMock);

        $instanceMock->expects($this->once())->method('getStoreFilter')->willReturn(true);

        $this->productMock->expects($this->once())->method('getStore')->willReturn($storeId);

        $this->pricingHelperMock->expects(
            $this->once()
        )->method(
            'currencyByStore'
        )->with(
            $price,
            $storeId,
            false
        )->willReturn(
            $expectedPrice
        );

        $this->assertEquals($expectedPrice, $this->block->getCurrencyPrice($price));
    }
}
