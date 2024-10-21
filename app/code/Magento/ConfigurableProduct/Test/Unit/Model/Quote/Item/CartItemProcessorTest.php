<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Quote\Item;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\Option;
use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor;
use Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory;
use Magento\ConfigurableProduct\Test\Unit\Model\Product\ProductOptionExtensionAttributes;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\Quote\Api\Data\ProductOptionInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\ProductOptionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemProcessorTest extends TestCase
{
    /**
     * @var CartItemProcessor
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

    /** @var MockObject */
    private $serializer;

    protected function setUp(): void
    {
        $this->objectFactoryMock = $this->createPartialMock(Factory::class, ['create']);
        $this->optionFactoryMock = $this->createPartialMock(
            ProductOptionFactory::class,
            ['create']
        );
        $this->optionExtensionFactoryMock = $this->createPartialMock(
            ProductOptionExtensionFactory::class,
            ['create']
        );
        $this->optionValueFactoryMock = $this->createPartialMock(
            ConfigurableItemOptionValueFactory::class,
            ['create']
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

        $this->serializer = $this->createMock(Json::class);

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

        $this->model = new CartItemProcessor(
            $this->objectFactoryMock,
            $this->optionFactoryMock,
            $this->optionExtensionFactoryMock,
            $this->optionValueFactoryMock,
            $this->serializer
        );
    }

    public function testConvertToBuyRequestIfNoProductOption()
    {
        $cartItemMock = $this->getMockForAbstractClass(CartItemInterface::class);
        $cartItemMock->expects($this->once())->method('getProductOption')->willReturn(null);
        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testConvertToBuyRequest()
    {
        $optionId = 'option_id';
        $optionValue = 'option_value';

        $productOptionMock = $this->getMockForAbstractClass(ProductOptionInterface::class);
        $cartItemMock = $this->getMockForAbstractClass(CartItemInterface::class);
        $cartItemMock->expects($this->exactly(3))->method('getProductOption')->willReturn($productOptionMock);
        $extAttributesMock = $this->getMockBuilder(ProductOptionInterface::class)
            ->addMethods(['getConfigurableItemOptions'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productOptionMock
            ->expects($this->exactly(2))
            ->method('getExtensionAttributes')
            ->willReturn($extAttributesMock);

        $optionValueMock = $this->createMock(
            ConfigurableItemOptionValueInterface::class
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
        $buyRequestMock = new DataObject($requestData);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with($requestData)
            ->willReturn($buyRequestMock);

        $this->assertEquals($buyRequestMock, $this->model->convertToBuyRequest($cartItemMock));
    }

    public function testProcessProductOptionsIfOptionNotSelected()
    {
        $customOption = $this->createMock(Option::class);
        $customOption->expects($this->once())->method('getValue')->willReturn('');

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->createPartialMock(Item::class, ['getProduct']);
        $cartItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptions()
    {
        $optionId = 'option_id';
        $optionValue = 'option_value';

        $customOption = $this->createMock(Option::class);
        $customOption->expects($this->once())->method('getValue')->willReturn(json_encode([$optionId => $optionValue]));
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->createPartialMock(
            Item::class,
            ['getProduct', 'getProductOption', 'setProductOption']
        );
        $cartItemMock->expects($this->once())->method('getProduct')->willReturn($productMock);
        $cartItemMock->expects($this->once())->method('getProductOption')->willReturn(null);

        $optionValueMock = $this->createMock(
            ConfigurableItemOptionValueInterface::class
        );
        $this->optionValueFactoryMock->expects($this->once())->method('create')->willReturn($optionValueMock);
        $optionValueMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $optionValueMock->expects($this->once())->method('setOptionValue')->with($optionValue)->willReturnSelf();

        $productOptionMock = $this->getMockForAbstractClass(ProductOptionInterface::class);
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

        $customOption = $this->createMock(Option::class);
        $customOption->expects($this->once())->method('getValue')->willReturn(json_encode([$optionId => $optionValue]));
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())->method('getCustomOption')->with('attributes')->willReturn($customOption);

        $cartItemMock = $this->createPartialMock(
            Item::class,
            ['getProduct', 'getProductOption', 'setProductOption']
        );

        $optionValueMock = $this->createMock(
            ConfigurableItemOptionValueInterface::class
        );
        $this->optionValueFactoryMock->expects($this->once())->method('create')->willReturn($optionValueMock);
        $optionValueMock->expects($this->once())->method('setOptionId')->with($optionId)->willReturnSelf();
        $optionValueMock->expects($this->once())->method('setOptionValue')->with($optionValue)->willReturnSelf();

        $this->productOptionExtensionAttributes->expects($this->once())
            ->method('setConfigurableItemOptions')
            ->with([$optionValueMock])
            ->willReturnSelf();

        $productOptionMock = $this->getMockForAbstractClass(ProductOptionInterface::class);
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

    /**
     * Checks processOptions method with the empty custom option
     *
     * @return void
     */
    public function testProcessProductWithEmptyOption(): void
    {
        $customOption = $this->createMock(Option::class);
        $productMock = $this->createMock(Product::class);
        $productMock->method('getCustomOption')
            ->with('attributes')
            ->willReturn(null);
        $customOption->expects($this->never())
            ->method('getValue');
        $cartItemMock = $this->createPartialMock(Item::class, ['getProduct']);
        $cartItemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);
        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }
}
