<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Wishlist\Controller\Index\Fromcart;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FromcartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Fromcart
     */
    protected $controller;

    /**
     * @var Context | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $context;

    /**
     * @var WishlistProviderInterface | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $wishlistProvider;

    /**
     * @var WishlistHelper | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $wishlistHelper;

    /**
     * @var CheckoutCart | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $cart;

    /**
     * @var CartHelper | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $cartHelper;

    /**
     * @var Escaper | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $escaper;

    /**
     * @var Http | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var MessageManager | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $messageManager;

    /**
     * @var ResultFactory | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultFactory;

    /**
     * @var ResultRedirect | \PHPUnit\Framework\MockObject\MockObject
     */
    protected $resultRedirect;

    /**
     * @var Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $formKeyValidator;

    protected function setUp(): void
    {
        $this->prepareContext();

        $this->wishlistProvider = $this->getMockBuilder(\Magento\Wishlist\Controller\WishlistProviderInterface::class)
            ->getMockForAbstractClass();

        $this->wishlistHelper = $this->getMockBuilder(\Magento\Wishlist\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cart = $this->getMockBuilder(\Magento\Checkout\Model\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartHelper = $this->getMockBuilder(\Magento\Checkout\Helper\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaper = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->formKeyValidator = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new Fromcart(
            $this->context,
            $this->wishlistProvider,
            $this->wishlistHelper,
            $this->cart,
            $this->cartHelper,
            $this->escaper,
            $this->formKeyValidator
        );
    }

    public function testExecuteWithInvalidFormKey()
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(false);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    /**
     */
    public function testExecutePageNotFound()
    {
        $this->expectException(\Magento\Framework\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Page not found');

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn(null);

        $this->controller->execute();
    }

    public function testExecuteNoCartItem()
    {
        $itemId = 1;
        $cartUrl = 'cart_url';

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $wishlistMock = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlistMock);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willReturn($itemId);

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn(null);

        $this->cart->expects($this->once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $this->cartHelper->expects($this->once())
            ->method('getCartUrl')
            ->willReturn($cartUrl);

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->with(__("The cart item doesn't exist."))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($cartUrl)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecute()
    {
        $itemId = 1;
        $cartUrl = 'cart_url';
        $productId = 1;
        $productName = 'product_name';

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $dataObjectMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $wishlistMock = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();
        $wishlistMock->expects($this->once())
            ->method('addNewItem')
            ->with($productId, $dataObjectMock)
            ->willReturnSelf();
        $wishlistMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlistMock);

        $this->wishlistHelper->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willReturn($itemId);

        $quoteMock = $this->createQuoteMock($productId, $productName, $dataObjectMock, $itemId);

        $this->cart->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($quoteMock);
        $this->cart->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->cartHelper->expects($this->once())
            ->method('getCartUrl')
            ->willReturn($cartUrl);

        $this->escaper->expects($this->once())
            ->method('escapeHtml')
            ->with($productName)
            ->willReturn($productName);

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with(__("%1 has been moved to your wish list.", $productName))
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($cartUrl)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecuteWithException()
    {
        $cartUrl = 'cart_url';
        $exceptionMessage = 'exception_message';
        $exception = new \Exception($exceptionMessage);

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $wishlistMock = $this->getMockBuilder(\Magento\Wishlist\Model\Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlistMock);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willThrowException($exception);

        $this->messageManager->expects($this->once())
            ->method('addExceptionMessage')
            ->with($exception, __('We can\'t move the item to the wish list.'))
            ->willReturnSelf();

        $this->cartHelper->expects($this->once())
            ->method('getCartUrl')
            ->willReturn($cartUrl);

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($cartUrl)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    protected function prepareContext()
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);
    }

    /**
     * @param int $productId
     * @param string $productName
     * @param DataObject $dataObjectMock
     * @param int $itemId
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createQuoteMock($productId, $productName, $dataObjectMock, $itemId)
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn($productName);

        $quoteItemMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Item::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getProductId',
                'getBuyRequest',
                'getProduct',
            ])
            ->getMock();
        $quoteItemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);
        $quoteItemMock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($dataObjectMock);
        $quoteItemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $quoteMock->expects($this->once())
            ->method('getItemById')
            ->with($itemId)
            ->willReturn($quoteItemMock);
        $quoteMock->expects($this->once())
            ->method('removeItem')
            ->with($itemId)
            ->willReturnSelf();

        return $quoteMock;
    }
}
