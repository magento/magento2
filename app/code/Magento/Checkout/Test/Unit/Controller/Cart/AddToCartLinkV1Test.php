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
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->_contextMock = $this->createMock(Context::class);
        $this->_checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->_productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->_resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->_messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->_requestMock = $this->createMock(RequestInterface::class);
        $this->_productMock = $this->createMock(Product::class);
        $this->_pageMock = $this->createMock(Page::class);
        
        // Create Quote mock with all required methods
        $this->_quoteMock = $this->getMockBuilder(Quote::class)
            ->onlyMethods(['removeAllItems', 'addProduct', 'collectTotals', 'save'])
            ->addMethods(['setCouponCode', 'getCouponCode'])
            ->disableOriginalConstructor()
            ->getMock();

        // Set up product mock to return IDs
        $this->_productMock->expects($this->any())
            ->method('getId')
            ->willReturn('12345');

        $this->_contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->_requestMock);

        $this->_controller = $objectManager->getObject(
            AddToCartLinkV1::class,
            [
                'context' => $this->_contextMock,
                'checkoutSession' => $this->_checkoutSessionMock,
                'productRepository' => $this->_productRepositoryMock,
                'resultPageFactory' => $this->_resultPageFactoryMock,
                'messageManager' => $this->_messageManagerMock
            ]
        );
    }

    /**
     * Test execute method with products and coupon
     *
     * @return void
     */
    public function testExecuteWithProductsAndCoupon(): void
    {
        $productsParam = '12345:2,67890:1';
        $couponCode = 'TESTCOUPON';
        $productId1 = '12345';
        $productId2 = '67890';
        $qty1 = 2;
        $qty2 = 1;

        // Set up request parameters
        $this->_requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['products', '', $productsParam],
                ['coupon', '', $couponCode]
            ]);

        // Set up checkout session to return quote
        $this->_checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        // Set up quote methods
        $this->_quoteMock->expects($this->once())
            ->method('removeAllItems');

        // Set up product repository - first try by SKU, then by ID
        // For first product: SKU lookup fails, ID lookup succeeds
        $this->_productRepositoryMock->expects($this->exactly(2))
            ->method('get')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Product not found')));

        $this->_productRepositoryMock->expects($this->exactly(2))
            ->method('getById')
            ->willReturnMap([
                [$productId1, false, null, false, $this->_productMock],
                [$productId2, false, null, false, $this->_productMock]
            ]);

        // Set up quote add product
        $this->_quoteMock->expects($this->exactly(2))
            ->method('addProduct')
            ->willReturnMap([
                [$this->_productMock, $qty1, $this->_quoteMock],
                [$this->_productMock, $qty2, $this->_quoteMock]
            ]);

        // Set up quote save and collect totals
        $this->_quoteMock->expects($this->exactly(2))
            ->method('collectTotals');
        $this->_quoteMock->expects($this->exactly(2))
            ->method('save');

        // Set up coupon code
        $this->_quoteMock->expects($this->once())
            ->method('setCouponCode')
            ->with($couponCode);

        $this->_quoteMock->expects($this->once())
            ->method('getCouponCode')
            ->willReturn($couponCode);

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

        // Set up request parameters
        $this->_requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['products', '', $productsParam],
                ['coupon', '', '']
            ]);

        // Set up checkout session to return quote
        $this->_checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        // Set up quote methods
        $this->_quoteMock->expects($this->once())
            ->method('removeAllItems');

        // Set up product repository to throw exception for both SKU and ID lookups
        $this->_productRepositoryMock->expects($this->once())
            ->method('get')
            ->with($productId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Product not found')));

        $this->_productRepositoryMock->expects($this->once())
            ->method('getById')
            ->with($productId)
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException(__('Product not found')));

        // Set up error message
        $this->_messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('Product with identifier "%1" was not found.', $productId));

        // Set up quote save and collect totals
        $this->_quoteMock->expects($this->once())
            ->method('collectTotals');
        $this->_quoteMock->expects($this->once())
            ->method('save');

        // Set up result page
        $this->_resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_pageMock);

        $result = $this->_controller->execute();
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertSame($this->_pageMock, $result);
    }

    /**
     * Test execute method with invalid coupon
     *
     * @return void
     */
    public function testExecuteWithInvalidCoupon(): void
    {
        $productsParam = '';
        $couponCode = 'INVALIDCOUPON';

        // Set up request parameters
        $this->_requestMock->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnMap([
                ['products', '', $productsParam],
                ['coupon', '', $couponCode]
            ]);

        // Set up checkout session to return quote
        $this->_checkoutSessionMock->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        // Set up quote methods
        $this->_quoteMock->expects($this->once())
            ->method('removeAllItems');

        // Set up coupon code
        $this->_quoteMock->expects($this->once())
            ->method('setCouponCode')
            ->with($couponCode);

        // Set up quote save and collect totals
        $this->_quoteMock->expects($this->once())
            ->method('collectTotals');
        $this->_quoteMock->expects($this->once())
            ->method('save');

        // Set up invalid coupon response
        $this->_quoteMock->expects($this->once())
            ->method('getCouponCode')
            ->willReturn('');

        // Set up error message
        $this->_messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with(__('The coupon code "%1" is not valid.', $couponCode));

        // Set up result page
        $this->_resultPageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->_pageMock);

        $result = $this->_controller->execute();
        $this->assertInstanceOf(ResultInterface::class, $result);
        $this->assertSame($this->_pageMock, $result);
    }
}
