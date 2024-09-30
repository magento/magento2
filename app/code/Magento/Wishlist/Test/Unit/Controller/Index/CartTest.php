<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Wishlist\Controller\Index\Cart;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ItemFactory;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Magento\Wishlist\Model\ResourceModel\Item\Option\Collection;
use Magento\Wishlist\Model\Wishlist;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends TestCase
{
    /**
     * @var Cart
     */
    protected $model;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var WishlistProviderInterface|MockObject
     */
    protected $wishlistProviderMock;

    /**
     * @var LocaleQuantityProcessor|MockObject
     */
    protected $quantityProcessorMock;

    /**
     * @var ItemFactory|MockObject
     */
    protected $itemFactoryMock;

    /**
     * @var CheckoutCart|MockObject
     */
    protected $checkoutCartMock;

    /**
     * @var OptionFactory|MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var ProductHelper|MockObject
     */
    protected $productHelperMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var Data|MockObject
     */
    protected $helperMock;

    /**
     * @var RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var RedirectInterface|MockObject
     */
    protected $redirectMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var UrlInterface|MockObject
     */
    protected $urlMock;

    /**
     * @var CartHelper|MockObject
     */
    protected $cartHelperMock;

    /**
     * @var ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var Redirect|MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var Json|MockObject
     */
    protected $resultJsonMock;

    /**
     * @var Validator|MockObject
     */
    protected $formKeyValidator;

    /**
     * @var CookieManagerInterface|MockObject
     */
    private $cookieManagerMock;

    /**
     * @var CookieMetadataFactory|MockObject
     */
    private $cookieMetadataFactoryMock;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->wishlistProviderMock = $this->getMockBuilder(
            WishlistProviderInterface::class
        )->disableOriginalConstructor()
            ->setMethods(['getWishlist'])
            ->getMockForAbstractClass();

        $this->quantityProcessorMock = $this->getMockBuilder(LocaleQuantityProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemFactoryMock = $this->getMockBuilder(ItemFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->checkoutCartMock = $this->getMockBuilder(CheckoutCart::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getQuote', 'getShouldRedirectToCart', 'getCartUrl'])
            ->getMock();

        $this->optionFactoryMock = $this->getMockBuilder(OptionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->productHelperMock = $this->getMockBuilder(ProductHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParams', 'getParam', 'isAjax', 'getPostValue'])
            ->getMockForAbstractClass();

        $this->redirectMock = $this->getMockBuilder(RedirectInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->messageManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addSuccessMessage'])
            ->getMockForAbstractClass();

        $this->urlMock = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
        $this->cartHelperMock = $this->getMockBuilder(CartHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->contextMock->expects($this->any())
            ->method('getRedirect')
            ->willReturn($this->redirectMock);
        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($this->messageManagerMock);
        $this->contextMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($this->urlMock);
        $this->contextMock->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($this->resultFactoryMock);
        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturnMap(
                [
                    [ResultFactory::TYPE_REDIRECT, [], $this->resultRedirectMock],
                    [ResultFactory::TYPE_JSON, [], $this->resultJsonMock]
                ]
            );

        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieManagerMock = $this->getMockForAbstractClass(CookieManagerInterface::class);

        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactoryMock = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['createPublicCookieMetadata'])
            ->getMock();
        $this->cookieMetadataFactoryMock->expects($this->any())
            ->method('createPublicCookieMetadata')
            ->willReturn($cookieMetadataMock);
        $cookieMetadataMock->expects($this->any())
            ->method('setDuration')
            ->willReturnSelf();
        $cookieMetadataMock->expects($this->any())
            ->method('setPath')
            ->willReturnSelf();
        $cookieMetadataMock->expects($this->any())
            ->method('setSameSite')
            ->willReturnSelf();
        $cookieMetadataMock->expects($this->any())
            ->method('setHttpOnly')
            ->willReturnSelf();

        $this->model = new Cart(
            $this->contextMock,
            $this->wishlistProviderMock,
            $this->quantityProcessorMock,
            $this->itemFactoryMock,
            $this->checkoutCartMock,
            $this->optionFactoryMock,
            $this->productHelperMock,
            $this->escaperMock,
            $this->helperMock,
            $this->cartHelperMock,
            $this->formKeyValidator,
            $this->cookieManagerMock,
            $this->cookieMetadataFactoryMock
        );
    }

    public function testExecuteWithInvalidFormKey()
    {
        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);

        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    public function testExecuteWithNoItem()
    {
        $itemId = false;

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item', null)
            ->willReturn($itemId);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->once())
            ->method('load')
            ->with($itemId, null)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*', [])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    public function testExecuteWithNoWishlist()
    {
        $itemId = 2;
        $wishlistId = 1;

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getId', 'getWishlistId'])
            ->getMock();

        $this->requestMock->expects($this->once())
            ->method('getParam')
            ->with('item', null)
            ->willReturn($itemId);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->once())
            ->method('load')
            ->with($itemId, null)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);

        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->with($wishlistId)
            ->willReturn(null);
        $this->resultRedirectMock->expects($this->once())
            ->method('setPath')
            ->with('*/*', [])
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    public function testExecuteWithQuantityArray()
    {
        $refererUrl = $this->prepareExecuteWithQuantityArray();

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($refererUrl)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    public function testExecuteWithQuantityArrayAjax()
    {
        $refererUrl = $this->prepareExecuteWithQuantityArray(true);

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->resultJsonMock->expects($this->once())
            ->method('setData')
            ->with(['backUrl' => $refererUrl])
            ->willReturnSelf();

        $this->assertSame($this->resultJsonMock, $this->model->execute());
    }

    /**
     * @param bool $isAjax
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function prepareExecuteWithQuantityArray($isAjax = false)
    {
        $itemId = 2;
        $wishlistId = 1;
        $qty = [$itemId => 3];
        $productId = 4;
        $productName = 'product_name';
        $indexUrl = 'index_url';
        $configureUrl = 'configure_url';
        $options = [5 => 'option'];
        $params = ['item' => $itemId, 'qty' => $qty];
        $refererUrl = 'referer_url';

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
                    'getId',
                    'getWishlistId',
                    'setQty',
                    'setOptions',
                    'getBuyRequest',
                    'mergeBuyRequest',
                    'addToCart',
                    'getProduct',
                    'getProductId',
                ]
            )
            ->getMock();

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item', null)
            ->willReturn($itemId);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->once())
            ->method('load')
            ->with($itemId, null)
            ->willReturnSelf();
        $itemMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->with($wishlistId)
            ->willReturn($wishlistMock);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('qty', null)
            ->willReturn($qty);

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with($qty[$itemId])
            ->willReturnArgument(0);

        $itemMock->expects($this->once())
            ->method('setQty')
            ->with($qty[$itemId])
            ->willReturnSelf();

        $this->urlMock->expects($this->at(0))
            ->method('getUrl')
            ->with('*/*', null)
            ->willReturn($indexUrl);

        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->urlMock->expects($this->at(1))
            ->method('getUrl')
            ->with('*/*/configure/', ['id' => $itemId, 'product_id' => $productId])
            ->willReturn($configureUrl);

        $optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $optionsMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($optionsMock);

        $optionsMock->expects($this->once())
            ->method('addItemFilter')
            ->with([$itemId])
            ->willReturnSelf();
        $optionsMock->expects($this->once())
            ->method('getOptionsByItem')
            ->with($itemId)
            ->willReturn($options);

        $itemMock->expects($this->once())
            ->method('setOptions')
            ->with($options)
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);
        $this->requestMock->expects($this->once())
            ->method('isAjax')
            ->willReturn($isAjax);

        $buyRequestMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($buyRequestMock);

        $this->productHelperMock->expects($this->once())
            ->method('addParamsToBuyRequest')
            ->with($params, ['current_config' => $buyRequestMock])
            ->willReturn($buyRequestMock);

        $itemMock->expects($this->once())
            ->method('mergeBuyRequest')
            ->with($buyRequestMock)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('addToCart')
            ->with($this->checkoutCartMock, true)
            ->willReturn(true);

        $this->checkoutCartMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getHasError', 'collectTotals'])
            ->getMock();

        $this->checkoutCartMock->expects($this->exactly(2))
            ->method('getQuote')
            ->willReturn($quoteMock);

        $quoteMock->expects($this->once())
            ->method('collectTotals')
            ->willReturnSelf();

        $wishlistMock->expects($this->once())
            ->method('save')
            ->willReturnSelf();

        $quoteMock->expects($this->once())
            ->method('getHasError')
            ->willReturn(false);

        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($productMock);

        $productMock->expects($this->atLeastOnce())
            ->method('getName')
            ->willReturn($productName);

        $this->messageManagerMock->expects($this->once())
            ->method('addComplexSuccessMessage')
            ->willReturnSelf();

        $this->cartHelperMock->expects($this->once())
            ->method('getShouldRedirectToCart')
            ->willReturn(false);

        $this->redirectMock->expects($this->once())
            ->method('getRefererUrl')
            ->willReturn($refererUrl);

        $this->helperMock->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        return $refererUrl;
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutQuantityArrayAndOutOfStock()
    {
        $itemId = 2;
        $wishlistId = 1;
        $qty = [];
        $productId = 4;
        $indexUrl = 'index_url';
        $configureUrl = 'configure_url';
        $options = [5 => 'option'];
        $params = ['item' => $itemId, 'qty' => $qty];

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
                    'getId',
                    'getWishlistId',
                    'setQty',
                    'setOptions',
                    'getBuyRequest',
                    'mergeBuyRequest',
                    'addToCart',
                    'getProduct',
                    'getProductId',
                ]
            )
            ->getMock();

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item', null)
            ->willReturn($itemId);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->once())
            ->method('load')
            ->with($itemId, null)
            ->willReturnSelf();
        $itemMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->with($wishlistId)
            ->willReturn($wishlistMock);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('qty', null)
            ->willReturn($qty);

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with(1)
            ->willReturnArgument(0);

        $itemMock->expects($this->once())
            ->method('setQty')
            ->with(1)
            ->willReturnSelf();

        $this->urlMock->expects($this->at(0))
            ->method('getUrl')
            ->with('*/*', null)
            ->willReturn($indexUrl);

        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->urlMock->expects($this->at(1))
            ->method('getUrl')
            ->with('*/*/configure/', ['id' => $itemId, 'product_id' => $productId])
            ->willReturn($configureUrl);

        $optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $optionsMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($optionsMock);

        $optionsMock->expects($this->once())
            ->method('addItemFilter')
            ->with([$itemId])
            ->willReturnSelf();
        $optionsMock->expects($this->once())
            ->method('getOptionsByItem')
            ->with($itemId)
            ->willReturn($options);

        $itemMock->expects($this->once())
            ->method('setOptions')
            ->with($options)
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);

        $buyRequestMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($buyRequestMock);

        $this->productHelperMock->expects($this->once())
            ->method('addParamsToBuyRequest')
            ->with($params, ['current_config' => $buyRequestMock])
            ->willReturn($buyRequestMock);

        $itemMock->expects($this->once())
            ->method('mergeBuyRequest')
            ->with($buyRequestMock)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('addToCart')
            ->with($this->checkoutCartMock, true)
            ->willThrowException(new ProductException(__('Test Phrase')));

        $this->messageManagerMock->expects($this->once())
            ->method('addErrorMessage')
            ->with('This product(s) is out of stock.', null)
            ->willReturnSelf();

        $this->helperMock->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($indexUrl)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutQuantityArrayAndConfigurable()
    {
        $itemId = 2;
        $wishlistId = 1;
        $qty = [];
        $productId = 4;
        $indexUrl = 'index_url';
        $configureUrl = 'configure_url';
        $options = [5 => 'option'];
        $params = ['item' => $itemId, 'qty' => $qty];

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
                    'getId',
                    'getWishlistId',
                    'setQty',
                    'setOptions',
                    'getBuyRequest',
                    'mergeBuyRequest',
                    'addToCart',
                    'getProduct',
                    'getProductId',
                ]
            )
            ->getMock();

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item', null)
            ->willReturn($itemId);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->once())
            ->method('load')
            ->with($itemId, null)
            ->willReturnSelf();
        $itemMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->with($wishlistId)
            ->willReturn($wishlistMock);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('qty', null)
            ->willReturn($qty);

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with(1)
            ->willReturnArgument(0);

        $itemMock->expects($this->once())
            ->method('setQty')
            ->with(1)
            ->willReturnSelf();

        $this->urlMock->expects($this->at(0))
            ->method('getUrl')
            ->with('*/*', null)
            ->willReturn($indexUrl);

        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->urlMock->expects($this->at(1))
            ->method('getUrl')
            ->with('*/*/configure/', ['id' => $itemId, 'product_id' => $productId])
            ->willReturn($configureUrl);

        $optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $optionsMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($optionsMock);

        $optionsMock->expects($this->once())
            ->method('addItemFilter')
            ->with([$itemId])
            ->willReturnSelf();
        $optionsMock->expects($this->once())
            ->method('getOptionsByItem')
            ->with($itemId)
            ->willReturn($options);

        $itemMock->expects($this->once())
            ->method('setOptions')
            ->with($options)
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);

        $buyRequestMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($buyRequestMock);

        $this->productHelperMock->expects($this->once())
            ->method('addParamsToBuyRequest')
            ->with($params, ['current_config' => $buyRequestMock])
            ->willReturn($buyRequestMock);

        $itemMock->expects($this->once())
            ->method('mergeBuyRequest')
            ->with($buyRequestMock)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('addToCart')
            ->with($this->checkoutCartMock, true)
            ->willThrowException(new LocalizedException(__('message')));

        $this->messageManagerMock->expects($this->once())
            ->method('addNoticeMessage')
            ->with('message', null)
            ->willReturnSelf();

        $this->helperMock->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($configureUrl)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithEditQuantity()
    {
        $itemId = 2;
        $wishlistId = 1;
        $qty = 1;
        $postQty = 2;
        $productId = 4;
        $indexUrl = 'index_url';
        $configureUrl = 'configure_url';
        $options = [5 => 'option'];
        $params = ['item' => $itemId, 'qty' => $qty];

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'load',
                    'getId',
                    'getWishlistId',
                    'setQty',
                    'setOptions',
                    'getBuyRequest',
                    'mergeBuyRequest',
                    'addToCart',
                    'getProduct',
                    'getProductId',
                ]
            )
            ->getMock();

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('item', null)
            ->willReturn($itemId);
        $this->itemFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($itemMock);

        $itemMock->expects($this->once())
            ->method('load')
            ->with($itemId, null)
            ->willReturnSelf();
        $itemMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn($itemId);
        $itemMock->expects($this->once())
            ->method('getWishlistId')
            ->willReturn($wishlistId);

        $wishlistMock = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->wishlistProviderMock->expects($this->once())
            ->method('getWishlist')
            ->with($wishlistId)
            ->willReturn($wishlistMock);

        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('qty', null)
            ->willReturn($qty);

        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->with('qty')
            ->willReturn($postQty);

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with($postQty)
            ->willReturnArgument(0);

        $itemMock->expects($this->once())
            ->method('setQty')
            ->with($postQty)
            ->willReturnSelf();

        $this->urlMock->expects($this->at(0))
            ->method('getUrl')
            ->with('*/*', null)
            ->willReturn($indexUrl);

        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->urlMock->expects($this->at(1))
            ->method('getUrl')
            ->with('*/*/configure/', ['id' => $itemId, 'product_id' => $productId])
            ->willReturn($configureUrl);

        $optionMock = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $optionsMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $optionMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($optionsMock);

        $optionsMock->expects($this->once())
            ->method('addItemFilter')
            ->with([$itemId])
            ->willReturnSelf();
        $optionsMock->expects($this->once())
            ->method('getOptionsByItem')
            ->with($itemId)
            ->willReturn($options);

        $itemMock->expects($this->once())
            ->method('setOptions')
            ->with($options)
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('getParams')
            ->willReturn($params);

        $buyRequestMock = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getBuyRequest')
            ->willReturn($buyRequestMock);

        $this->productHelperMock->expects($this->once())
            ->method('addParamsToBuyRequest')
            ->with($params, ['current_config' => $buyRequestMock])
            ->willReturn($buyRequestMock);

        $itemMock->expects($this->once())
            ->method('mergeBuyRequest')
            ->with($buyRequestMock)
            ->willReturnSelf();
        $itemMock->expects($this->once())
            ->method('addToCart')
            ->with($this->checkoutCartMock, true)
            ->willThrowException(new LocalizedException(__('message')));

        $this->messageManagerMock->expects($this->once())
            ->method('addNoticeMessage')
            ->with('message', null)
            ->willReturnSelf();

        $this->helperMock->expects($this->once())
            ->method('calculate')
            ->willReturnSelf();

        $this->resultRedirectMock->expects($this->once())
            ->method('setUrl')
            ->with($configureUrl)
            ->willReturnSelf();

        $this->assertSame($this->resultRedirectMock, $this->model->execute());
    }
}
