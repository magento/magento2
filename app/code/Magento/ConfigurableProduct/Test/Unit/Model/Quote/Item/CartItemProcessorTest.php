<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Quote\Item;

use Magento\ConfigurableProduct\Test\Unit\Model\Product\ProductOptionExtensionAttributes;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $objectFactoryMock;

    /**
     * @var MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var MockObject
     */
    protected $optionExtensionFactoryMock;

    /**
     * @var MockObject
     */
    protected $optionValueFactoryMock;

    /**
     * @var ProductOptionExtensionAttributes|MockObject
     */
    private $productOptionExtensionAttributes;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $serializer;

    protected function setUp()
    {
        $this->objectFactoryMock = $this->getMock(
            \Magento\Framework\DataObject\Factory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionFactoryMock = $this->getMock(
            \Magento\Quote\Model\Quote\ProductOptionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionExtensionFactoryMock = $this->getMock(
            \Magento\Quote\Api\Data\ProductOptionExtensionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->optionValueFactoryMock = $this->getMock(
            \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory::class,
            ['create'],
            [],
            '',
            false
        );

        $this->productOptionExtensionAttributes = $this->getMockForAbstractClass(
            ProductOptionExtensionAttributes::class,
            [],
            '',
            false,
            true,
            true,
            ['setConfigurableItemOptions']
        );

        $this->serializer = $this->getMock(
            \Magento\Framework\Serialize\Serializer\Json::class,
            [],
            [],
            '',
            false
        );

        $this->serializer->expects($this->any())
            ->method('serialize')
            ->willReturnCallback(
                function ($value) {
                    return json_encode($value);
                }
            );

        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(
                function ($value) {
                    return json_decode($value, true);
                }
            );

        $this->model = new \Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor(
            $this->objectFactoryMock,
            $this->optionFactoryMock,
            $this->optionExtensionFactoryMock,
            $this->optionValueFactoryMock,
            $this->serializer
        );
    }

    public function testConvertToBuyRequestIfNoProductOption()
    {
        $cartItemMock = $this->getMock(\Magento\Quote\Api\Data\CartItemInterface::class);
        $cartItemMock->expects($this->once())->method('getProductOption')->willReturn(null);
        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testConvertToBuyRequest()
    {
        $optionId = 'option_id';
        $optionValue = 'option_value';

        $productOptionMock = $this->getMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);
        $cartItemMock = $this->getMock(\Magento\Quote\Api\Data\CartItemInterface::class);
        $cartItemMock->expects($this->exactly(3))->method('getProductOption')->willReturn($productOptionMock);
        $extAttributesMock = $this->getMock(
            \Magento\Quote\Api\Data\ProductOption::class,
            ['getConfigurableItemOptions'],
            [],
            '',
            false
        );
        $productOptionMock
            ->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($extAttributesMock);

        $optionValueMock = $this->getMock(
            \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface::class
        );
        $extAttributesMock->expects($this->once())
            ->method('getConfigurableItemOptions')
            ->willReturn([$optionValueMock]);

        $optionValueMock->expects($this->once())->method('getOptionId')->willReturn($optionId);
        $optionValueMock->expects($this->once())->method('getOptionValue')->willReturn($optionValue);

        $requestData = [
            'super_attribute' => [
                $optionId => $optionValue
            ]
        ];
        $buyRequestMock = new \Magento\Framework\DataObject($requestData);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with($requestData)
            ->willReturn($buyRequestMock);

        $this->assertEquals($buyRequestMock, $this->model->convertToBuyRequest($cartItemMock));
    }

    public function testProcessProductOptionsIfOptionNotSelected()
    {
        $customOption = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option::class,
            [],
            [],
            '',
            false
        );
        $customOption->expects($this->once())->method('getValue')->willReturn('');

        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->getMock(\Magento\Quote\Model\Quote\Item::class, ['getProduct'], [], '', false);
        $cartItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptions()
    {
        $optionId = 'option_id';
        $optionValue = 'option_value';

        $customOption = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option::class,
            [],
            [],
            '',
            false
        );
        $customOption->expects($this->once())->method('getValue')->willReturn(json_encode([$optionId => $optionValue]));
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'getProductOption', 'setProductOption'],
            [],
            '',
            false
        );
        $cartItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $cartItemMock->expects($this->once())->method('getProductOption')->willReturn(null);

        $optionValueMock = $this->getMock(
            \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface::class
        );
        $this->optionValueFactoryMock->expects($this->once())->method('create')->willReturn($optionValueMock);
        $optionValueMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $optionValueMock->expects($this->once())->method('setOptionValue')->with($optionValue)->willReturnSelf();

        $productOptionMock = $this->getMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($productOptionMock);
        $productOptionMock->expects($this->once())->method('getExtensionAttributes')->willReturn(null);

        $this->optionExtensionFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->productOptionExtensionAttributes);
        $this->productOptionExtensionAttributes->expects($this->once())
            ->method('setConfigurableItemOptions')
            ->with([$optionValueMock])
            ->willReturnSelf();
        $productOptionMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->productOptionExtensionAttributes)
            ->willReturnSelf();
        $cartItemMock->expects($this->once())->method('setProductOption')->with($productOptionMock)->willReturnSelf();

        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptionsIfOptionsExist()
    {
        $optionId = 'option_id';
        $optionValue = 'option_value';

        $customOption = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option::class,
            [],
            [],
            '',
            false
        );
        $customOption->expects($this->once())->method('getValue')->willReturn(json_encode([$optionId => $optionValue]));
        $productMock = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->getMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'getProductOption', 'setProductOption'],
            [],
            '',
            false
        );

        $optionValueMock = $this->getMock(
            \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface::class
        );
        $this->optionValueFactoryMock->expects($this->once())->method('create')->willReturn($optionValueMock);
        $optionValueMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $optionValueMock->expects($this->once())->method('setOptionValue')->with($optionValue)->willReturnSelf();

        $this->productOptionExtensionAttributes->expects($this->once())
            ->method('setConfigurableItemOptions')
            ->with([$optionValueMock])
            ->willReturnSelf();

        $productOptionMock = $this->getMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);
        $productOptionMock->expects(static::exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($this->productOptionExtensionAttributes);

        $cartItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $cartItemMock->expects($this->exactly(2))->method('getProductOption')->willReturn($productOptionMock);

        $productOptionMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->productOptionExtensionAttributes)
            ->willReturnSelf();
        $cartItemMock->expects($this->once())->method('setProductOption')->with($productOptionMock)->willReturnSelf();

        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }
}
