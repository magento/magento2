<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Quote\Item;

use Magento\Downloadable\Model\Quote\Item\CartItemProcessor;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CartItemProcessor
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $objectHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $optionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $extensionFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $downloadableOptionFactoryMock;

    protected function setUp()
    {
        $this->objectFactoryMock = $this->createPartialMock(\Magento\Framework\DataObject\Factory::class, ['create']);
        $this->optionFactoryMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\ProductOptionFactory::class,
            ['create']
        );
        $this->objectHelperMock = $this->createMock(\Magento\Framework\Api\DataObjectHelper::class);
        $this->extensionFactoryMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\ProductOptionExtensionFactory::class,
            ['create']
        );
        $this->downloadableOptionFactoryMock = $this->createPartialMock(
            \Magento\Downloadable\Model\DownloadableOptionFactory::class,
            ['create']
        );

        $this->model = new CartItemProcessor(
            $this->objectFactoryMock,
            $this->objectHelperMock,
            $this->downloadableOptionFactoryMock,
            $this->optionFactoryMock,
            $this->extensionFactoryMock
        );
    }

    public function testConvertToBuyRequestReturnsNullIfItemDoesNotContainProductOption()
    {
        $cartItemMock = $this->createMock(\Magento\Quote\Api\Data\CartItemInterface::class);
        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testConvertToBuyRequest()
    {
        $downloadableLinks = [1, 2];
        $itemQty = 1;

        $cartItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProductOption', 'setProductOption', 'getOptionByCode', 'getQty']
        );
        $productOptionMock = $this->createMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);

        $cartItemMock->expects($this->any())->method('getProductOption')->willReturn($productOptionMock);
        $cartItemMock->expects($this->any())->method('getQty')->willReturn($itemQty);
        $extAttributesMock = $this->getMockBuilder(\Magento\Quote\Api\Data\ProductOptionInterface::class)
            ->setMethods(['getDownloadableOption'])
            ->getMockForAbstractClass();
        $productOptionMock->expects($this->any())->method('getExtensionAttributes')->willReturn($extAttributesMock);

        $downloadableOptionMock = $this->createMock(\Magento\Downloadable\Api\Data\DownloadableOptionInterface::class);
        $extAttributesMock->expects($this->any())
            ->method('getDownloadableOption')
            ->willReturn($downloadableOptionMock);

        $downloadableOptionMock->expects($this->any())->method('getDownloadableLinks')->willReturn($downloadableLinks);

        $buyRequestData = [
            'links' => $downloadableLinks,
        ];
        $buyRequestMock = new \Magento\Framework\DataObject($buyRequestData);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with($buyRequestData)
            ->willReturn($buyRequestMock);

        $this->assertEquals($buyRequestMock, $this->model->convertToBuyRequest($cartItemMock));
    }

    public function testProcessProductOptions()
    {
        $downloadableLinks = [1, 2];

        $customOption = $this->createMock(\Magento\Catalog\Model\Product\Configuration\Item\Option::class);
        $customOption->expects($this->once())->method('getValue')->willReturn(implode(',', $downloadableLinks));

        $cartItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'getProductOption', 'setProductOption', 'getOptionByCode']
        );
        $cartItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('downloadable_link_ids')
            ->willReturn($customOption);

        $cartItemMock->expects($this->any())
            ->method('getProductOption')
            ->willReturn(null);

        $downloadableOptionMock = $this->createMock(\Magento\Downloadable\Api\Data\DownloadableOptionInterface::class);
        $this->downloadableOptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($downloadableOptionMock);

        $productOptionMock = $this->createMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($productOptionMock);
        $productOptionMock->expects($this->once())->method('getExtensionAttributes')->willReturn(null);

        $extAttributeMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\ProductOptionExtension::class,
            ['setDownloadableOption']
        );

        $this->objectHelperMock->expects($this->once())->method('populateWithArray')->with(
            $downloadableOptionMock,
            [
                'downloadable_links' => $downloadableLinks
            ],
            \Magento\Downloadable\Api\Data\DownloadableOptionInterface::class
        );

        $this->extensionFactoryMock->expects($this->once())->method('create')->willReturn($extAttributeMock);
        $extAttributeMock->expects($this->once())
            ->method('setDownloadableOption')
            ->with($downloadableOptionMock);
        $productOptionMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extAttributeMock);
        $cartItemMock->expects($this->once())->method('setProductOption')->with($productOptionMock);

        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }

    public function testProcessProductOptionsWhenItemDoesNotHaveDownloadableLinks()
    {
        $downloadableLinks = [];

        $cartItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getProduct', 'getProductOption', 'setProductOption', 'getOptionByCode']
        );
        $cartItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('downloadable_link_ids');

        $extAttributeMock = $this->createPartialMock(
            \Magento\Quote\Api\Data\ProductOptionExtension::class,
            ['setDownloadableOption']
        );
        $productOptionMock = $this->createMock(\Magento\Quote\Api\Data\ProductOptionInterface::class);
        $productOptionMock->expects($this->any())
            ->method('getExtensionAttributes')
            ->willReturn($extAttributeMock);
        $cartItemMock->expects($this->any())
            ->method('getProductOption')
            ->willReturn($productOptionMock);

        $downloadableOptionMock = $this->createMock(\Magento\Downloadable\Api\Data\DownloadableOptionInterface::class);
        $this->downloadableOptionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($downloadableOptionMock);

        $this->optionFactoryMock->expects($this->never())->method('create');
        $this->extensionFactoryMock->expects($this->never())->method('create');

        $this->objectHelperMock->expects($this->once())->method('populateWithArray')->with(
            $downloadableOptionMock,
            [
                'downloadable_links' => $downloadableLinks
            ],
            \Magento\Downloadable\Api\Data\DownloadableOptionInterface::class
        );

        $extAttributeMock->expects($this->once())
            ->method('setDownloadableOption')
            ->with($downloadableOptionMock);
        $productOptionMock->expects($this->once())
            ->method('setExtensionAttributes')
            ->with($extAttributeMock);
        $cartItemMock->expects($this->once())->method('setProductOption')->with($productOptionMock);

        $this->assertEquals($cartItemMock, $this->model->processOptions($cartItemMock));
    }
}
