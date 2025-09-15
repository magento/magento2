<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Plugin\Webapi;

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Plugin\Webapi\ValidateProductWebsiteAssignment;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ValidateProductWebsiteAssignment plugin
 */
class ValidateProductWebsiteAssignmentTest extends TestCase
{
    /**
     * @var ValidateProductWebsiteAssignment
     */
    private $plugin;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $cartRepositoryMock;

    /**
     * @var CartItemRepositoryInterface|MockObject
     */
    private $cartItemRepositoryMock;

    /**
     * @var CartItemInterface|MockObject
     */
    private $cartItemMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var ProductResource|MockObject
     */
    private $productResourceMock;

    /**
     * @var ProductWebsiteLink|MockObject
     */
    private $productWebsiteLinkMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->cartItemRepositoryMock = $this->createMock(CartItemRepositoryInterface::class);
        $this->cartItemMock = $this->createMock(CartItemInterface::class);
        $this->quoteMock = $this->createMock(Quote::class);
        $this->storeMock = $this->createMock(StoreInterface::class);

        $this->productResourceMock = $this->createMock(ProductResource::class);
        $this->productWebsiteLinkMock = $this->createMock(ProductWebsiteLink::class);

        $this->plugin = new ValidateProductWebsiteAssignment(
            $this->storeManagerMock,
            $this->cartRepositoryMock,
            $this->productResourceMock,
            $this->productWebsiteLinkMock
        );
    }

    /**
     * Test successful validation when product is assigned to current website
     */
    public function testBeforeSaveSuccessful()
    {
        $sku = 'test-product';
        $quoteId = 1;
        $storeId = 1;
        $productId = 123;
        $websiteId = 1;
        $productWebsiteIds = [1, 2];

        $this->cartItemMock->expects($this->once())
            ->method('getSku')
            ->willReturn($sku);

        $this->cartItemMock->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($quoteId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productResourceMock->expects($this->once())
            ->method('getIdBySku')
            ->with($sku)
            ->willReturn($productId);

        $this->productWebsiteLinkMock->expects($this->once())
            ->method('getWebsiteIdsByProductId')
            ->with($productId)
            ->willReturn($productWebsiteIds);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->plugin->beforeSave($this->cartItemRepositoryMock, $this->cartItemMock);
        $this->assertTrue(true);
    }

    /**
     * Test validation when product is not assigned to current website
     */
    public function testBeforeSaveProductNotAssignedToWebsite()
    {
        $sku = 'test-product';
        $quoteId = 1;
        $storeId = 1;
        $productId = 123;
        $websiteId = 1;
        $productWebsiteIds = [2, 3];

        $this->cartItemMock->expects($this->once())
            ->method('getSku')
            ->willReturn($sku);

        $this->cartItemMock->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($quoteId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productResourceMock->expects($this->once())
            ->method('getIdBySku')
            ->with($sku)
            ->willReturn($productId);

        $this->productWebsiteLinkMock->expects($this->once())
            ->method('getWebsiteIdsByProductId')
            ->with($productId)
            ->willReturn($productWebsiteIds);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Product that you are trying to add is not available.');

        $this->plugin->beforeSave($this->cartItemRepositoryMock, $this->cartItemMock);
    }

    /**
     * Test validation skips when no SKU provided
     */
    public function testBeforeSaveNoSku()
    {
        $this->cartItemMock->expects($this->once())
            ->method('getSku')
            ->willReturn(null);

        // No further method calls expected since validation should return early
        // Method returns void, so we just verify no exception is thrown
        $this->plugin->beforeSave($this->cartItemRepositoryMock, $this->cartItemMock);

        // If we reach this point, validation passed
        $this->assertTrue(true);
    }

    /**
     * Test validation skips when empty SKU provided
     */
    public function testBeforeSaveEmptySku()
    {
        $this->cartItemMock->expects($this->once())
            ->method('getSku')
            ->willReturn('');

        // No further method calls expected since validation should return early
        // Method returns void, so we just verify no exception is thrown
        $this->plugin->beforeSave($this->cartItemRepositoryMock, $this->cartItemMock);

        // If we reach this point, validation passed
        $this->assertTrue(true);
    }

    /**
     * Test validation when product is assigned to multiple websites including current
     */
    public function testBeforeSaveMultipleWebsitesIncludingCurrent()
    {
        $sku = 'test-product';
        $quoteId = 1;
        $storeId = 1;
        $productId = 123;
        $websiteId = 2;
        $productWebsiteIds = [1, 2, 3];

        $this->cartItemMock->expects($this->once())
            ->method('getSku')
            ->willReturn($sku);

        $this->cartItemMock->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($quoteId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productResourceMock->expects($this->once())
            ->method('getIdBySku')
            ->with($sku)
            ->willReturn($productId);

        $this->productWebsiteLinkMock->expects($this->once())
            ->method('getWebsiteIdsByProductId')
            ->with($productId)
            ->willReturn($productWebsiteIds);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->plugin->beforeSave($this->cartItemRepositoryMock, $this->cartItemMock);
        $this->assertTrue(true);
    }

    /**
     * Test validation when website ID is zero
     */
    public function testBeforeSaveWebsiteIdZero()
    {
        $sku = 'test-product';
        $quoteId = 1;
        $storeId = 1;
        $productId = 123;
        $websiteId = 0;
        $productWebsiteIds = [0, 1];

        $this->cartItemMock->expects($this->once())
            ->method('getSku')
            ->willReturn($sku);

        $this->cartItemMock->expects($this->once())
            ->method('getQuoteId')
            ->willReturn($quoteId);

        $this->cartRepositoryMock->expects($this->once())
            ->method('getActive')
            ->with($quoteId)
            ->willReturn($this->quoteMock);

        $this->quoteMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $this->productResourceMock->expects($this->once())
            ->method('getIdBySku')
            ->with($sku)
            ->willReturn($productId);

        $this->productWebsiteLinkMock->expects($this->once())
            ->method('getWebsiteIdsByProductId')
            ->with($productId)
            ->willReturn($productWebsiteIds);

        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->with($storeId)
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->once())
            ->method('getWebsiteId')
            ->willReturn($websiteId);

        $this->plugin->beforeSave($this->cartItemRepositoryMock, $this->cartItemMock);
        $this->assertTrue(true);
    }
}
