<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Shared;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Exception;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Wishlist\Controller\Shared\Cart as SharedCart;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Option\Collection as OptionCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Wishlist\Controller\Shared\Cart.
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends TestCase
{
    /**
     * @var SharedCart|MockObject
     */
    private $model;

    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var ManagerInterface|MockObject
     */
    private $messageManager;

    /**
     * @var Cart|MockObject
     */
    private $cart;

    /**
     * @var CartHelper|MockObject
     */
    private $cartHelper;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    /**
     * @var OptionCollection|MockObject
     */
    private $optionCollection;

    /**
     * @var Option|MockObject
     */
    private $option;

    /**
     * @var Item|MockObject
     */
    private $item;

    /**
     * @var Escaper|MockObject
     */
    private $escaper;

    /**
     * @var RedirectInterface|MockObject
     */
    private $redirect;

    /**
     * @var Redirect|MockObject
     */
    private $resultRedirect;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->request = $this->getMockForAbstractClass(RequestInterface::class);
        $this->redirect = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->resultRedirect = $this->createMock(Redirect::class);

        $resultFactory = $this->createMock(ResultFactory::class);
        $resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        /** @var ActionContext|MockObject $context */
        $context = $this->getMockBuilder(ActionContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $context->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);
        $context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultFactory);

        $this->cart = $this->createMock(Cart::class);
        $this->cartHelper = $this->createMock(CartHelper::class);

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getHasError'])
            ->getMock();

        $this->optionCollection = $this->createMock(OptionCollection::class);

        $this->option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var OptionFactory|MockObject $optionFactory */
        $optionFactory = $this->createMock(OptionFactory::class);
        $optionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->option);

        $this->item = $this->createMock(Item::class);

        $itemFactory = $this->createMock(ItemFactory::class);
        $itemFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->item);

        $this->escaper = $this->createMock(Escaper::class);
        $this->product = $this->createMock(Product::class);

        $this->model = new SharedCart(
            $context,
            $this->cart,
            $optionFactory,
            $itemFactory,
            $this->cartHelper,
            $this->escaper
        );
    }

    /**
     * @param int $itemId
     * @param string $productName
     * @param bool $hasErrors
     * @param bool $redirectToCart
     * @param string $refererUrl
     * @param string $cartUrl
     * @param string $redirectUrl
     *
     * @dataProvider dataProviderExecute
     */
    public function testExecute(
        $itemId,
        $productName,
        $hasErrors,
        $redirectToCart,
        $refererUrl,
        $cartUrl,
        $redirectUrl
    ) {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willReturn($itemId);

        $this->redirect->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $this->option->expects($this->once())
            ->method('getCollection')
            ->willReturn($this->optionCollection);

        $this->optionCollection->expects($this->once())
            ->method('addItemFilter')
            ->with([$itemId])
            ->willReturnSelf();
        $this->optionCollection->expects($this->once())
            ->method('getOptionsByItem')
            ->with($itemId)
            ->willReturn([]);

        $this->item->expects($this->once())
            ->method('load')
            ->with($itemId)
            ->willReturnSelf();
        $this->item->expects($this->once())
            ->method('setOptions')
            ->with([])
            ->willReturnSelf();
        $this->item->expects($this->once())
            ->method('addToCart')
            ->with($this->cart)
            ->willReturnSelf();
        $this->item->expects($this->any())
            ->method('getProduct')
            ->willReturn($this->product);

        $this->quote->expects($this->once())
            ->method('getHasError')
            ->willReturn($hasErrors);

        $this->cart->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);
        $this->cart->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $this->cartHelper->expects($this->once())
            ->method('getShouldRedirectToCart')
            ->willReturn($redirectToCart);
        $this->cartHelper->expects($this->any())
            ->method('getCartUrl')
            ->willReturn($cartUrl);

        $this->product->expects($this->any())
            ->method('getName')
            ->willReturn($productName);

        $this->escaper->expects($this->any())
            ->method('escapeHtml')
            ->with($productName)
            ->willReturn($productName);

        $successMessage = __('You added %1 to your shopping cart.', $productName);
        $this->messageManager->expects($this->any())
            ->method('addSuccessMessage')
            ->with($successMessage)
            ->willReturnSelf();

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($redirectUrl)
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    /**
     * 1. Wishlist Item ID
     * 2. Product Name
     * 3. Quote has errors
     * 4. Should redirect to Cart flag
     * 5. Referer URL
     * 6. Shopping Cart URL
     * 7. Redirect URL (RESULT)
     *
     * @return array
     */
    public static function dataProviderExecute()
    {
        return [
            [1, 'product_name', false, true, 'referer_url', 'cart_url', 'cart_url'],
            [1, 'product_name', true, false, 'referer_url', 'cart_url', 'referer_url'],
        ];
    }

    public function testExecuteLocalizedException()
    {
        $itemId = 1;
        $refererUrl = 'referer_url';
        $productUrl = 'product_url';

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willReturn($itemId);

        $this->item->expects($this->once())
            ->method('load')
            ->with($itemId)
            ->willReturnSelf();
        $this->item->expects($this->once())
            ->method('getProductUrl')
            ->willReturn($productUrl);

        $this->redirect->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $this->option->expects($this->once())
            ->method('getCollection')
            ->willThrowException(new LocalizedException(__('LocalizedException')));

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($productUrl)
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    public function testExecuteProductException()
    {
        $itemId = 1;
        $refererUrl = 'referer_url';

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willReturn($itemId);

        $this->item->expects($this->once())
            ->method('load')
            ->with($itemId)
            ->willReturnSelf();

        $this->redirect->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $this->option->expects($this->once())
            ->method('getCollection')
            ->willThrowException(new Exception(__('LocalizedException')));

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($refererUrl)
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }

    public function testExecuteException()
    {
        $itemId = 1;
        $refererUrl = 'referer_url';

        $this->request->expects($this->once())
            ->method('getParam')
            ->with('item')
            ->willReturn($itemId);

        $this->item->expects($this->once())
            ->method('load')
            ->with($itemId)
            ->willReturnSelf();

        $this->redirect->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $this->option->expects($this->once())
            ->method('getCollection')
            ->willThrowException(new \Exception('Exception'));

        $this->resultRedirect->expects($this->once())
            ->method('setUrl')
            ->with($refererUrl)
            ->willReturnSelf();

        $this->assertEquals($this->resultRedirect, $this->model->execute());
    }
}
