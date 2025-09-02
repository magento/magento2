<?php
declare(strict_types=1);

/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Controller\Cart\AddToCartLinkV1;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\Store;

/**
 * Test for AddToCartLinkV1 controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddToCartLinkV1Test extends TestCase
{
    /**
     * Instance of the class under test
     *
     * @var AddToCartLinkV1
     */
    private $_controller;

    /**
     * Application context object
     *
     * @var Context|MockObject
     */
    private $_contextMock;

    /**
     * Shopping cart checkout session
     *
     * @var CheckoutSession|MockObject
     */
    private $_checkoutSessionMock;

    /**
     * Interface for product repository operations
     *
     * @var ProductRepositoryInterface|MockObject
     */
    private $_productRepositoryMock;

    /**
     * Factory for creating result pages
     *
     * @var PageFactory|MockObject
     */
    private $_resultPageFactoryMock;

    /**
     * Interface for managing messages and notifications
     *
     * @var ManagerInterface|MockObject
     */
    private $_messageManagerMock;

    /**
     * HTTP request object interface
     *
     * @var RequestInterface|MockObject
     */
    private $_requestMock;

    /**
     * Shopping cart quote model
     *
     * @var Quote|MockObject
     */
    private $_quoteMock;

    /**
     * Catalog product model
     *
     * @var Product|MockObject
     */
    private $_productMock;

    /**
     * Result page object
     *
     * @var Page|MockObject
     */
    private $_pageMock;

    /**
     * Scope config mock
     *
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * Forward factory mock
     *
     * @var ForwardFactory|MockObject
     */
    private $resultForwardFactoryMock;

    /**
     * Store manager mock
     *
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * Cart repository mock
     *
     * @var CartRepositoryInterface|MockObject
     */
    private $cartRepositoryMock;

    /**
     * Store mock
     *
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->_contextMock = $this->createMock(Context::class);
        $this->_checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->_productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->_resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->_messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->_requestMock = $this->createMock(RequestInterface::class);
        $this->_productMock = $this->createMock(Product::class);
        $this->_pageMock = $this->createMock(Page::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->resultForwardFactoryMock = $this->createMock(ForwardFactory::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->cartRepositoryMock = $this->createMock(CartRepositoryInterface::class);
        $this->storeMock = $this->createMock(Store::class);
        
        // Create Quote mock with all required methods
        $this->_quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['removeAllItems', 'addProduct', 'collectTotals', 'save', 'getStoreId', 'setStoreId'])
            ->disableOriginalConstructor()
            ->getMock();

        // Set up product mock to return IDs
        $this->_productMock->expects($this->any())
            ->method('getId')
            ->willReturn('12345');
            
        $this->_productMock->expects($this->any())
            ->method('getStoreIds')
            ->willReturn([1]); 

        $this->_contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->_requestMock);
            
        // Default store setup for StoreManager mock
        $this->storeMock->expects($this->any())->method('getId')->willReturn(1);
        $this->storeMock->expects($this->any())->method('getIsActive')->willReturn(true);
        $this->storeManagerMock->expects($this->any())->method('getStore')->willReturn($this->storeMock);
        $this->storeManagerMock->expects($this->any())->method('getDefaultStoreView')->willReturn($this->storeMock);

        $this->_controller = new AddToCartLinkV1(
            $this->_contextMock,
            $this->_checkoutSessionMock,
            $this->_productRepositoryMock,
            $this->_resultPageFactoryMock,
            $this->_messageManagerMock,
            $this->scopeConfigMock,
            $this->resultForwardFactoryMock,
            $this->storeManagerMock,
            $this->cartRepositoryMock
        );
    }

    /**
     * Test execute method with products
     *
     * @return void
     */
    public function testExecuteWithProducts(): void
    {
        $productsParam = '12345:2,67890:1';
        $productId1 = '12345';
        $productId2 = '67890';
        $qty1 = 2;
        $qty2 = 1;

        // Enable feature flag
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AddToCartLinkV1::XML_PATH_ENABLE_ADD_TO_CART_LINK, 'store')
            ->willReturn(true);

        // Set up request parameters (now expecting store param as well)
        $this->_requestMock->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap([
                ['store', null, null],
                ['products', '', $productsParam],
                ['coupon', '', '']
            ]);

        // Set up checkout session to return quote
        $this->_checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);
            
        // Mock getStoreId for Quote
        $this->_quoteMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);

        // Set up quote methods
        $this->_quoteMock->expects($this->once())
            ->method('removeAllItems');

        // Set up product repository - first try by SKU, then by ID
        // For first product: SKU lookup fails, ID lookup succeeds
        $this->_productRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive([$productId1], [$productId2])
            ->willReturn($this->_productMock);

        // Set up quote save and collect totals
        $this->_quoteMock->expects($this->once())
            ->method('collectTotals');
        $this->cartRepositoryMock->expects($this->once())
             ->method('save')
             ->with($this->_quoteMock);

        // Set up result page
        $this->_resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_pageMock);

        $result = $this->_controller->execute();
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertSame($this->_pageMock, $result);
    }

    /**
     * Test execute method with invalid product
     *
     * @return void
     */
    public function testExecuteWithInvalidProduct(): void
    {
        $productsParam = '12345:2';
        $productId = '12345';

        // Enable feature flag
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(AddToCartLinkV1::XML_PATH_ENABLE_ADD_TO_CART_LINK, 'store')
            ->willReturn(true);

        // Set up request parameters
        $this->_requestMock->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnMap([
                ['store', null, null],
                ['products', '', $productsParam],
                ['coupon', '', '']
            ]);

        // Set up checkout session to return quote
        $this->_checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);
            
        // Mock getStoreId for Quote
        $this->_quoteMock->expects($this->any())
            ->method('getStoreId')
            ->willReturn(1);

        // Set up quote methods
        $this->_quoteMock->expects($this->once())
            ->method('removeAllItems');

        // Set up product repository to throw exception for SKU lookup
        $this->_productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Product not found')));

        // Set up product repository to throw exception for ID lookup
        $this->_productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Product not found')));

        // Set up quote save and collect totals
        $this->_quoteMock->expects($this->once())
            ->method('collectTotals');
        $this->cartRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->_quoteMock);

        // Set up result page
        $this->_resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_pageMock);

        $result = $this->_controller->execute();
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertSame($this->_pageMock, $result);
    }
}
