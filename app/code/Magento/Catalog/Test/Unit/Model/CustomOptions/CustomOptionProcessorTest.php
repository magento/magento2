<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\CustomOptions;

use Magento\Catalog\Model\CustomOptions\CustomOptionProcessor;

class CustomOptionProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DataObject\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ProductOptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productOptionFactory;

    /**
     * @var \Magento\Quote\Api\Data\ProductOptionExtensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionFactory;

    /**
     * @var \Magento\Catalog\Model\CustomOptions\CustomOptionFactory
     * |\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customOptionFactory;

    /** @var \Magento\Quote\Api\Data\CartItemInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cartItem;

    /** @var \Magento\Quote\Api\Data\ProductOptionExtensionInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $extensibleAttribute;

    /** @var \Magento\Quote\Model\Quote\ProductOption|\PHPUnit_Framework_MockObject_MockObject */
    protected $productOption;
    
    /** @var \Magento\Catalog\Model\CustomOptions\CustomOption|\PHPUnit_Framework_MockObject_MockObject */
    protected $customOption;

    /** @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject */
    protected $buyRequest;

    /** @var CustomOptionProcessor */
    protected $processor;

    protected function setUp()
    {
        $this->objectFactory = $this->getMockBuilder('Magento\Framework\DataObject\Factory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productOptionFactory = $this->getMockBuilder('Magento\Quote\Model\Quote\ProductOptionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->extensionFactory = $this->getMockBuilder('Magento\Quote\Api\Data\ProductOptionExtensionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customOptionFactory = $this->getMockBuilder('Magento\Catalog\Model\CustomOptions\CustomOptionFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItem = $this->getMockBuilder('Magento\Quote\Api\Data\CartItemInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getOptionByCode', 'getProductOption', 'setProductOption'])
            ->getMockForAbstractClass();
        $this->extensibleAttribute = $this->getMockBuilder('Magento\Quote\Api\Data\ProductOptionExtensionInterface')
            ->disableOriginalConstructor()
            ->setMethods(['setCustomOptions', 'getCustomOptions'])
            ->getMockForAbstractClass();
        $this->productOption = $this->getMockBuilder('Magento\Quote\Model\Quote\ProductOption')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customOption = $this->getMockBuilder('Magento\Catalog\Api\Data\CustomOptionInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->buyRequest = $this->getMockBuilder('Magento\Framework\DataObject')
            ->disableOriginalConstructor()
            ->getMock();

        $this->processor = new CustomOptionProcessor(
            $this->objectFactory,
            $this->productOptionFactory,
            $this->extensionFactory,
            $this->customOptionFactory
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

    public function testProcessCustomOptions()
    {
        $optionId = 23;
        $quoteItemOption = $this->getMockBuilder('Magento\Quote\Model\Quote\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cartItem->expects($this->atLeastOnce())
            ->method('getOptionByCode')
            ->with('info_buyRequest')
            ->willReturn($quoteItemOption);
        $quoteItemOption->expects($this->once())
            ->method('getValue')
            ->willReturn('a:1:{s:7:"options";a:1:{i:' . $optionId . ';a:2:{i:0;s:1:"5";i:1;s:1:"6";}}} ');
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
