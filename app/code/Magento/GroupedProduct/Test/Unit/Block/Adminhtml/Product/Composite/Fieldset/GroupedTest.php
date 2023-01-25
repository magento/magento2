<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Block\Adminhtml\Product\Composite\Fieldset;

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
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $customerMock = $this->getMockBuilder(
            CustomerInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $customerMock->expects($this->any())->method('getId')->willReturn(1);

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

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getProduct
     */
    public function testGetProductPositive()
    {
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $storeMock = $this->createMock(Store::class);

        $this->productMock->expects($this->any())->method('getTypeInstance')->willReturn($instanceMock);

        $instanceMock->expects($this->once())->method('getStoreFilter')->willReturn($storeMock);

        $instanceMock->expects($this->never())->method('setStoreFilter');

        $this->assertEquals($this->productMock, $this->block->getProduct());
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getProduct
     */
    public function testGetProductNegative()
    {
        $storeId = 2;
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);
        $storeMock = $this->createMock(Store::class);

        $this->productMock->expects($this->any())->method('getTypeInstance')->willReturn($instanceMock);

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

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getAssociatedProducts
     */
    public function testGetAssociatedProducts()
    {
        $storeId = 2;

        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->productMock->expects($this->any())->method('getTypeInstance')->willReturn($instanceMock);

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

        $this->productMock->expects($this->any())->method('getStoreId')->willReturn($storeId);

        $this->assertEquals([$associatedProduct], $this->block->getAssociatedProducts());
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::setPreconfiguredValue
     */
    public function testSetPreconfiguredValue()
    {
        $storeId = 2;

        $objectMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['getSuperGroup'])
            ->disableOriginalConstructor()
            ->getMock();
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $objectMock->expects($this->once())->method('getSuperGroup')->willReturn([]);

        $this->productMock->expects(
            $this->once()
        )->method(
            'getPreconfiguredValues'
        )->willReturn(
            $objectMock
        );

        $this->productMock->expects($this->any())->method('getTypeInstance')->willReturn($instanceMock);

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

        $this->productMock->expects($this->any())->method('getStoreId')->willReturn($storeId);

        $this->assertEquals($this->block, $this->block->setPreconfiguredValue());
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getCanShowProductPrice
     */
    public function testGetCanShowProductPrice()
    {
        $this->assertTrue($this->block->getCanShowProductPrice($this->productMock));
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getIsLastFieldset
     */
    public function testGetIsLastFieldsetPositive()
    {
        $this->block->setData('is_last_fieldset', true);

        $this->productMock->expects($this->never())->method('getOptions');

        $this->assertTrue($this->block->getIsLastFieldset());
    }

    /**
     * @param array|bool $options
     * @param bool $expectedResult
     *
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getIsLastFieldset
     * @dataProvider getIsLastFieldsetDataProvider
     */
    public function testGetIsLastFieldsetNegative($options, $expectedResult)
    {
        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->block->setData('is_last_fieldset', false);

        $this->productMock->expects($this->once())->method('getOptions')->willReturn($options);

        $this->productMock->expects($this->any())->method('getTypeInstance')->willReturn($instanceMock);

        $instanceMock->expects($this->once())->method('getStoreFilter')->willReturn(true);

        $this->assertEquals($expectedResult, $this->block->getIsLastFieldset());
    }

    /**
     * Data provider for testGetIsLastFieldsetNegative
     *
     * @return array
     */
    public function getIsLastFieldsetDataProvider()
    {
        return [
            'case1' => ['options' => false, 'expectedResult' => true],
            'case2' => ['options' => ['option'], 'expectedResult' => false]
        ];
    }

    /**
     * @covers \Magento\GroupedProduct\Block\Adminhtml\Product\Composite\Fieldset\Grouped::getCurrencyPrice
     */
    public function testGetCurrencyPrice()
    {
        $storeId = 2;
        $price = 1.22;
        $expectedPrice = 1;

        $instanceMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->productMock->expects($this->any())->method('getTypeInstance')->willReturn($instanceMock);

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
