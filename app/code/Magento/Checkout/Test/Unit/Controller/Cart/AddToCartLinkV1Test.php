<?php
declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Controller\Cart\AddToCartLinkV1;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\Usage;
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
     * Shopping cart model instance
     *
     * @var Cart|MockObject
     */
    private $_cartMock;

    /**
     * Factory for creating result pages
     *
     * @var PageFactory|MockObject
     */
    private $_resultPageFactoryMock;

    /**
     * Factory for creating redirect responses
     *
     * @var RedirectFactory|MockObject
     */
    private $_resultRedirectFactoryMock;

    /**
     * Factory for creating coupon models
     *
     * @var CouponFactory|MockObject
     */
    private $_couponFactoryMock;

    /**
     * Resource model for coupon usage tracking
     *
     * @var Usage|MockObject
     */
    private $_couponUsageMock;

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
        $this->_cartMock = $this->createMock(Cart::class);
        $this->_resultPageFactoryMock = $this->createMock(PageFactory::class);
        $this->_resultRedirectFactoryMock = $this->createMock(RedirectFactory::class);
        $this->_couponFactoryMock = $this->createMock(CouponFactory::class);
        $this->_couponUsageMock = $this->createMock(Usage::class);
        $this->_messageManagerMock = $this->createMock(ManagerInterface::class);
        $this->_requestMock = $this->createMock(RequestInterface::class);
        $this->_productMock = $this->createMock(Product::class);
        $this->_pageMock = $this->createMock(Page::class);
        
        // Create Quote mock using getMockBuilder with addMethods
        $this->_quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setCouponCode', 'getCouponCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->_contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->_requestMock);

        $this->_controller = $objectManager->getObject(
            AddToCartLinkV1::class,
            [
                'context' => $this->_contextMock,
                'checkoutSession' => $this->_checkoutSessionMock,
                'productRepository' => $this->_productRepositoryMock,
                'cart' => $this->_cartMock,
                'resultPageFactory' => $this->_resultPageFactoryMock,
                'resultRedirectFactory' => $this->_resultRedirectFactoryMock,
                'couponFactory' => $this->_couponFactoryMock,
                'couponUsage' => $this->_couponUsageMock,
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

        // Set up cart truncate
        $this->_cartMock->expects($this->once())
            ->method('truncate');

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

        // Set up product mock to return IDs
        $this->_productMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturnOnConsecutiveCalls($productId1, $productId2);

        // Set up cart add product - now using product ID
        $this->_cartMock->expects($this->exactly(2))
            ->method('addProduct')
            ->willReturnMap([
                [$productId1, ['qty' => $qty1], $this->_cartMock],
                [$productId2, ['qty' => $qty2], $this->_cartMock]
            ]);

        // Set up cart save
        $this->_cartMock->expects($this->exactly(2))
            ->method('save');

        // Set up quote
        $this->_cartMock->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

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

        // Set up cart truncate
        $this->_cartMock->expects($this->once())
            ->method('truncate');

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

        // Set up cart save
        $this->_cartMock->expects($this->once())
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

        // Set up cart truncate
        $this->_cartMock->expects($this->once())
            ->method('truncate');

        // Set up quote
        $this->_cartMock->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($this->_quoteMock);

        // Set up coupon code
        $this->_quoteMock->expects($this->once())
            ->method('setCouponCode')
            ->with($couponCode);

        // Set up cart save
        $this->_cartMock->expects($this->once())
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
