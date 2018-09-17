<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Shared;

use Magento\Catalog\Model\Product;
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

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends \PHPUnit_Framework_TestCase
{
    /** @var  SharedCart |\PHPUnit_Framework_MockObject_MockObject */
    protected $model;

    /** @var  RequestInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var  ManagerInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var  ActionContext |\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var  Cart |\PHPUnit_Framework_MockObject_MockObject */
    protected $cart;

    /** @var  CartHelper |\PHPUnit_Framework_MockObject_MockObject */
    protected $cartHelper;

    /** @var  Quote | \PHPUnit_Framework_MockObject_MockObject */
    protected $quote;

    /** @var  OptionCollection |\PHPUnit_Framework_MockObject_MockObject */
    protected $optionCollection;

    /** @var  OptionFactory |\PHPUnit_Framework_MockObject_MockObject */
    protected $optionFactory;

    /** @var  Option |\PHPUnit_Framework_MockObject_MockObject */
    protected $option;

    /** @var  ItemFactory |\PHPUnit_Framework_MockObject_MockObject */
    protected $itemFactory;

    /** @var  Item |\PHPUnit_Framework_MockObject_MockObject */
    protected $item;

    /** @var  Escaper |\PHPUnit_Framework_MockObject_MockObject */
    protected $escaper;

    /** @var  RedirectInterface |\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    /** @var  ResultFactory |\PHPUnit_Framework_MockObject_MockObject */
    protected $resultFactory;

    /** @var  Redirect |\PHPUnit_Framework_MockObject_MockObject */
    protected $resultRedirect;

    /** @var  Product |\PHPUnit_Framework_MockObject_MockObject */
    protected $product;

    protected function setUp()
    {
        $this->request = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->getMockForAbstractClass();

        $this->redirect = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->getMockForAbstractClass();

        $this->messageManager = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->getMockForAbstractClass();

        $this->resultRedirect = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultFactory = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactory->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->resultRedirect);

        $this->context = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->request);
        $this->context->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirect);
        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);
        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactory);

        $this->cart = $this->getMockBuilder('Magento\Checkout\Model\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cartHelper = $this->getMockBuilder('Magento\Checkout\Helper\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        $this->quote = $this->getMockBuilder('Magento\Quote\Model\Quote')
            ->disableOriginalConstructor()
            ->setMethods(['getHasError'])
            ->getMock();

        $this->optionCollection = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Option\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $this->option = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionFactory = $this->getMockBuilder('Magento\Wishlist\Model\Item\OptionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->optionFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->option);

        $this->item = $this->getMockBuilder('Magento\Wishlist\Model\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemFactory = $this->getMockBuilder('Magento\Wishlist\Model\ItemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->itemFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->item);

        $this->escaper = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->product = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new SharedCart(
            $this->context,
            $this->cart,
            $this->optionFactory,
            $this->itemFactory,
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
            ->method('addSuccess')
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
    public function dataProviderExecute()
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
            ->willThrowException(new \Magento\Catalog\Model\Product\Exception(__('LocalizedException')));

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
