<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Quote\Item;

use Magento\Catalog\Model\Product\Configuration\Item\Option;
use Magento\Downloadable\Api\Data\DownloadableOptionInterface;
use Magento\Downloadable\Model\DownloadableOptionFactory;
use Magento\Downloadable\Model\Quote\Item\CartItemProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ProductOptionExtension;
use Magento\Quote\Api\Data\ProductOptionExtensionFactory;
use Magento\Quote\Api\Data\ProductOptionInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\ProductOptionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartItemProcessorTest extends TestCase
{
    /**
     * @var CartItemProcessor
     */
    private $model;

    /**
     * @var MockObject
     */
    private $objectFactoryMock;

    /**
     * @var MockObject
     */
    private $objectHelperMock;

    /**
     * @var MockObject
     */
    private $optionFactoryMock;

    /**
     * @var MockObject
     */
    private $extensionFactoryMock;

    /**
     * @var MockObject
     */
    protected $downloadableOptionFactoryMock;

    protected function setUp(): void
    {
        $this->objectFactoryMock = $this->createPartialMock(Factory::class, ['create']);
        $this->optionFactoryMock = $this->createPartialMock(
            ProductOptionFactory::class,
            ['create']
        );
        $this->objectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->extensionFactoryMock = $this->createPartialMock(
            ProductOptionExtensionFactory::class,
            ['create']
        );
        $this->downloadableOptionFactoryMock = $this->createPartialMock(
            DownloadableOptionFactory::class,
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
        $cartItemMock = $this->createMock(CartItemInterface::class);
        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testConvertToBuyRequest()
    {
        $downloadableLinks = [1, 2];
        $itemQty = 1;

        $cartItemMock = $this->createPartialMock(
            Item::class,
            ['getProductOption', 'setProductOption', 'getOptionByCode', 'getQty']
        );
        $productOptionMock = $this->createMock(ProductOptionInterface::class);

        $cartItemMock->method('getProductOption')->willReturn($productOptionMock);
        $cartItemMock->method('getQty')->willReturn($itemQty);
        $extAttributesMock = $this->createPartialMock(
            \Magento\Quote\Test\Unit\Helper\ProductOptionExtensionTestHelper::class,
            ['getDownloadableOption']
        );
        $productOptionMock->method('getExtensionAttributes')->willReturn($extAttributesMock);

        $downloadableOptionMock = $this->createMock(DownloadableOptionInterface::class);
        $extAttributesMock->method('getDownloadableOption')->willReturn($downloadableOptionMock);

        $downloadableOptionMock->method('getDownloadableLinks')->willReturn($downloadableLinks);

        $buyRequestData = [
            'links' => $downloadableLinks,
        ];
        $buyRequestMock = new DataObject($buyRequestData);
        $this->objectFactoryMock->expects($this->once())
            ->method('create')
            ->with($buyRequestData)
            ->willReturn($buyRequestMock);

        $this->assertEquals($buyRequestMock, $this->model->convertToBuyRequest($cartItemMock));
    }

    public function testConvertToBuyRequestWithoutExtensionAttributes()
    {
        $cartItemMock = $this->createPartialMock(
            Item::class,
            ['getProductOption', 'setProductOption', 'getOptionByCode', 'getQty']
        );
        $productOptionMock = $this->createMock(ProductOptionInterface::class);

        $cartItemMock->method('getProductOption')->willReturn($productOptionMock);
        $productOptionMock->expects($this->atLeastOnce())->method('getExtensionAttributes')->willReturn(null);

        $this->assertNull($this->model->convertToBuyRequest($cartItemMock));
    }

    public function testProcessProductOptions()
    {
        $downloadableLinks = [1, 2];

        $customOption = $this->createMock(Option::class);
        $customOption->expects($this->once())->method('getValue')->willReturn(implode(',', $downloadableLinks));

        $cartItemMock = $this->createPartialMock(
            Item::class,
            ['getProduct', 'getProductOption', 'setProductOption', 'getOptionByCode']
        );
        $cartItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('downloadable_link_ids')
            ->willReturn($customOption);

        $cartItemMock->method('getProductOption')->willReturn(null);

        $downloadableOptionMock = $this->createMock(DownloadableOptionInterface::class);
        $this->downloadableOptionFactoryMock->method('create')->willReturn($downloadableOptionMock);

        $productOptionMock = $this->createMock(ProductOptionInterface::class);
        $this->optionFactoryMock->expects($this->once())->method('create')->willReturn($productOptionMock);
        $productOptionMock->expects($this->once())->method('getExtensionAttributes')->willReturn(null);

        $extAttributeMock = $this->getProductOptionExtensionMock();

        $this->objectHelperMock->expects($this->once())->method('populateWithArray')->with(
            $downloadableOptionMock,
            [
                'downloadable_links' => $downloadableLinks,
            ],
            DownloadableOptionInterface::class
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
            Item::class,
            ['getProduct', 'getProductOption', 'setProductOption', 'getOptionByCode']
        );
        $cartItemMock->expects($this->once())
            ->method('getOptionByCode')
            ->with('downloadable_link_ids');

        $extAttributeMock = $this->getProductOptionExtensionMock();
        $productOptionMock = $this->createMock(ProductOptionInterface::class);
        $productOptionMock->method('getExtensionAttributes')->willReturn($extAttributeMock);
        $cartItemMock->method('getProductOption')->willReturn($productOptionMock);

        $downloadableOptionMock = $this->createMock(DownloadableOptionInterface::class);
        $this->downloadableOptionFactoryMock->method('create')->willReturn($downloadableOptionMock);

        $this->optionFactoryMock->expects($this->never())->method('create');
        $this->extensionFactoryMock->expects($this->never())->method('create');

        $this->objectHelperMock->expects($this->once())->method('populateWithArray')->with(
            $downloadableOptionMock,
            [
                'downloadable_links' => $downloadableLinks,
            ],
            DownloadableOptionInterface::class
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

    /**
     * Build product option extension mock.
     *
     * @return MockObject
     */
    private function getProductOptionExtensionMock(): MockObject
    {
        return $this->createPartialMock(
            \Magento\Quote\Test\Unit\Helper\ProductOptionExtensionTestHelper::class,
            ['setDownloadableOption']
        );
    }
}
