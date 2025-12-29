<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\Redirect as ResultRedirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\Manager as FrameworkMessageManager;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Exception;
use Magento\Framework\Message\Manager as MessageManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Wishlist\Controller\Index\Fromcart;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FromcartTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Fromcart
     */
    protected $controller;

    /**
     * @var Context|MockObject
     */
    protected $context;

    /**
     * @var WishlistProviderInterface|MockObject
     */
    protected $wishlistProvider;

    /**
     * @var WishlistHelper|MockObject
     */
    protected $wishlistHelper;

    /**
     * @var CheckoutCart|MockObject
     */
    protected $cart;

    /**
     * @var CartHelper|MockObject
     */
    protected $cartHelper;

    /**
     * @var Escaper|MockObject
     */
    protected $escaper;

    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var MessageManager|MockObject
     */
    protected $messageManager;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactory;

    /**
     * @var ResultRedirect|MockObject
     */
    protected $resultRedirect;

    /**
     * @var Validator|MockObject
     */
    protected $formKeyValidator;

    protected function setUp(): void
    {
        $this->prepareContext();

        $this->wishlistProvider = $this->createMock(WishlistProviderInterface::class);

        $this->wishlistHelper = $this->createMock(WishlistHelper::class);

        $this->cart = $this->createMock(Cart::class);

        $this->cartHelper = $this->createMock(CartHelper::class);

        $this->escaper = $this->createMock(Escaper::class);

        $this->formKeyValidator = $this->createMock(Validator::class);

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

    public function testExecutePageNotFound()
    {
        $this->expectException('Magento\Framework\Exception\NotFoundException');
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

        $wishlistMock = $this->createMock(Wishlist::class);

        $this->wishlistProvider->expects($this->once())
            ->method('getWishlist')
            ->willReturn($wishlistMock);

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willReturn($itemId);

        $quoteMock = $this->createMock(Quote::class);

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

        $dataObjectMock = $this->createMock(DataObject::class);

        $wishlistMock = $this->createMock(Wishlist::class);
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
        $exception = new Exception($exceptionMessage);

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->request)
            ->willReturn(true);

        $wishlistMock = $this->createMock(Wishlist::class);

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
        $this->request = $this->createMock(Http::class);

        $this->messageManager = $this->createMock(FrameworkMessageManager::class);

        $this->resultRedirect = $this->createMock(Redirect::class);

        $this->resultFactory = $this->createMock(ResultFactory::class);
        $this->resultFactory->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $this->context = $this->createMock(Context::class);

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
     * @return MockObject
     */
    protected function createQuoteMock($productId, $productName, $dataObjectMock, $itemId)
    {
        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn($productName);

        $quoteItemMock = $this->createPartialMockWithReflection(
            Item::class,
            ['getProductId', 'getBuyRequest', 'getProduct']
        );
        $quoteItemMock->method('getProductId')->willReturn($productId);
        $quoteItemMock->method('getBuyRequest')->willReturn($dataObjectMock);
        $quoteItemMock->method('getProduct')->willReturn($productMock);

        $quoteMock = $this->createMock(Quote::class);
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
