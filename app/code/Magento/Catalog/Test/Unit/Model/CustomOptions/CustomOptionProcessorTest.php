<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\CustomOptions;

use Magento\Catalog\Api\Data\CustomOptionInterface;
use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\CustomOptions\CustomOptionFactory;
use Magento\Catalog\Model\CustomOptions\CustomOptionProcessor;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\Quote\Api\Data\ProductOptionExtensionInterface;
use Magento\Quote\Model\Quote\Item\Option;
use Magento\Quote\Model\Quote\ProductOption;
use Magento\Quote\Model\Quote\ProductOptionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomOptionProcessorTest extends TestCase
{
    /**
     * @var Factory|MockObject
     */
    protected $objectFactory;

    /**
     * @var ProductOptionFactory|MockObject
     */
    protected $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory|MockObject
     */
    protected $extensionFactory;

    /**
     * @var CustomOptionFactory|MockObject
     */
    protected $customOptionFactory;

    /** @var CartItemInterface|MockObject */
    protected $cartItem;

    /** @var ProductOptionExtensionInterface|MockObject */
    protected $extensibleAttribute;

    /** @var ProductOption|MockObject */
    protected $productOption;

    /** @var CustomOption|MockObject */
    protected $customOption;

    /** @var DataObject|MockObject */
    protected $buyRequest;

    /** @var CustomOptionProcessor */
    protected $processor;

    /** @var Json */
    private $serializer;

    protected function setUp(): void
    {
        $this->objectFactory = $this->getMockBuilder(Factory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productOptionFactory = $this->getMockBuilder(ProductOptionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionFactory = $this->getMockBuilder(ProductOptionExtensionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customOptionFactory = $this->getMockBuilder(
            CustomOptionFactory::class
        )
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItem = $this->getMockBuilder(CartItemInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOptionByCode'])
            ->onlyMethods(['getProductOption', 'setProductOption'])
            ->getMockForAbstractClass();
        $this->extensibleAttribute = $this->getMockBuilder(
            ProductOptionExtensionInterface::class
        )
            ->disableOriginalConstructor()
            ->addMethods(['setCustomOptions', 'getCustomOptions'])
            ->getMockForAbstractClass();
        $this->productOption = $this->getMockBuilder(ProductOption::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customOption = $this->getMockBuilder(CustomOptionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->buyRequest = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMockBuilder(Json::class)
            ->onlyMethods(['unserialize'])
            ->getMockForAbstractClass();

        $this->processor = new CustomOptionProcessor(
            $this->objectFactory,
            $this->productOptionFactory,
            $this->extensionFactory,
            $this->customOptionFactory,
            $this->serializer
        );
    }

    public function testConvertToBuyRequest()
    {
        $optionId = 23;
        $optionValue = 'Option value';
        $this->objectFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->buyRequest);
        $this->cartItem->expects($this->any())
            ->method('getProductOption')
            ->willReturn($this->productOption);
        $this->productOption->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($this->extensibleAttribute);
        $this->extensibleAttribute->expects($this->atLeastOnce())
            ->method('getCustomOptions')
            ->willReturn([$this->customOption]);
        $this->customOption->expects($this->once())
            ->method('getOptionId')
            ->willReturn($optionId);
        $this->customOption->expects($this->once())
            ->method('getOptionValue')
            ->willReturn($optionValue);

        $this->assertSame($this->buyRequest, $this->processor->convertToBuyRequest($this->cartItem));
    }

    /**
     * @covers \Magento\Catalog\Model\CustomOptions\CustomOptionProcessor::getOptions()
     */
    public function testProcessCustomOptions()
    {
        $optionId = 23;
        $quoteItemOption = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItem->expects($this->atLeastOnce())
            ->method('getOptionByCode')
            ->with('info_buyRequest')
            ->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->any())
            ->method('getValue')
            ->willReturn('{"options":{"' . $optionId . '":["5","6"]}}');
        $this->serializer->expects($this->any())
            ->method('unserialize')
            ->willReturn(json_decode($quoteItemOption->getValue(), true));
        $this->customOptionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->customOption);
        $this->customOption->expects($this->once())
            ->method('setOptionId')
            ->with($optionId);
        $this->customOption->expects($this->once())
            ->method('setOptionValue')
            ->with('5,6');
        $this->cartItem->expects($this->atLeastOnce())
            ->method('getProductOption')
            ->willReturn(false);
        $this->productOptionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->productOption);
        $this->productOption->expects($this->once())
            ->method('getExtensionAttributes')
            ->willReturn(false);
        $this->extensionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->extensibleAttribute);
        $this->extensibleAttribute->expects($this->once())
            ->method('setCustomOptions')
            ->with([$optionId => $this->customOption]);
        $this->productOption->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($this->extensibleAttribute);
        $this->cartItem->expects($this->once())
            ->method('setProductOption')
            ->with($this->productOption);

        $this->assertSame($this->cartItem, $this->processor->processOptions($this->cartItem));
    }
}
