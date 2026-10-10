<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Checkout\Helper\Cart as CartHelper;
use Magento\Checkout\Model\Cart as CheckoutCart;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as RequestHttp;
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
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends TestCase
{
    use MockCreationTrait;

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
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->wishlistProviderMock = $this->createPartialMock(WishlistProviderInterface::class, ['getWishlist']);

        $this->quantityProcessorMock = $this->createMock(LocaleQuantityProcessor::class);

        $this->itemFactoryMock = $this->createPartialMock(ItemFactory::class, ['create']);

        $this->checkoutCartMock = $this->createPartialMockWithReflection(
            CheckoutCart::class,
            ['save', 'getQuote', 'getShouldRedirectToCart', 'getCartUrl']
        );

        $this->optionFactoryMock = $this->createPartialMock(OptionFactory::class, ['create']);

        $this->productHelperMock = $this->createMock(ProductHelper::class);

        $this->escaperMock = $this->createMock(Escaper::class);

        $this->helperMock = $this->createMock(Data::class);

        $this->requestMock = $this->createPartialMock(
            RequestHttp::class,
            ['getParams', 'getParam', 'isAjax', 'getPostValue']
        );

        $this->redirectMock = $this->createMock(RedirectInterface::class);

        $this->objectManagerMock = $this->createMock(ObjectManagerInterface::class);

        $this->messageManagerMock = $this->createMock(ManagerInterface::class);

        $this->urlMock = $this->createMock(UrlInterface::class);

        $this->cartHelperMock = $this->createMock(CartHelper::class);

        $this->resultFactoryMock = $this->createMock(ResultFactory::class);

        $this->resultRedirectMock = $this->createMock(Redirect::class);

        $this->resultJsonMock = $this->createMock(Json::class);

        $this->contextMock = $this->createMock(Context::class);
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

        $this->cookieManagerMock = $this->createMock(CookieManagerInterface::class);

        $cookieMetadataMock = $this->getMockBuilder(PublicCookieMetadata::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cookieMetadataFactoryMock = $this->getMockBuilder(CookieMetadataFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['createPublicCookieMetadata'])
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

    /**
     * @return void
     */
    public function testExecuteWithInvalidFormKey(): void
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

    /**
     * @return void
     */
    public function testExecuteWithNoItem(): void
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

    /**
     * @return void
     */
    public function testExecuteWithNoWishlist(): void
    {
        $itemId = 2;
        $wishlistId = 1;

        $this->formKeyValidator->expects($this->once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $itemMock = $this->createPartialMockWithReflection(
            Item::class,
            ['load', 'getId', 'getWishlistId']
        );

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

    /**
     * @return void
     */
    public function testExecuteWithQuantityArray(): void
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

    /**
     * @return void
     */
    public function testExecuteWithQuantityArrayAjax(): void
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
     *
     * @return string
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function prepareExecuteWithQuantityArray($isAjax = false): string
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

        $itemMock = $this->createPartialMockWithReflection(
            Item::class,
            [
                'load',
                'getId',
                'setQty',
                'setOptions',
                'getBuyRequest',
                'mergeBuyRequest',
                'addToCart',
                'getProduct',
                'getWishlistId',
                'getProductId'
            ]
        );

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

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($itemId, $qty) {
                if ($arg1 == 'item') {
                    return $itemId;
                } elseif ($arg1 == 'qty') {
                    return $qty;
                }
            });

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with($qty[$itemId])
            ->willReturnArgument(0);

        $itemMock->expects($this->once())
            ->method('setQty')
            ->with($qty[$itemId])
            ->willReturnSelf();

        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->urlMock
            ->method('getUrl')
            ->willReturnCallback(function ($arg1, $arg2) use ($indexUrl, $configureUrl, $itemId, $productId) {
                if ($arg1 == '*/*' && is_null($arg2)) {
                    return $indexUrl;
                } elseif ($arg1 == '*/*/configure/' && $arg2['id'] == $itemId && $arg2['product_id'] == $productId) {
                    return $configureUrl;
                }
            });

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

        $quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            ['collectTotals', 'getHasError']
        );

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
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutQuantityArrayAndOutOfStock(): void
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

        $itemMock = $this->createPartialMockWithReflection(
            Item::class,
            [
                'load',
                'getId',
                'setQty',
                'setOptions',
                'getBuyRequest',
                'mergeBuyRequest',
                'addToCart',
                'getProduct',
                'getWishlistId',
                'getProductId'
            ]
        );

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

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($itemId, $qty) {
                if ($arg1 == 'item') {
                    return $itemId;
                } elseif ($arg1 == 'qty') {
                    return $qty;
                }
            });

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with(1)
            ->willReturnArgument(0);

        $itemMock->expects($this->once())
            ->method('setQty')
            ->with(1)
            ->willReturnSelf();

        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->urlMock
            ->method('getUrl')
            ->willReturnCallback(function ($arg1, $arg2) use ($indexUrl, $configureUrl, $itemId, $productId) {
                if ($arg1 == '*/*' && is_null($arg2)) {
                    return $indexUrl;
                } elseif ($arg1 == '*/*/configure/' && $arg2['id'] == $itemId && $arg2['product_id'] == $productId) {
                    return $configureUrl;
                }
            });

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
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithoutQuantityArrayAndConfigurable(): void
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

        $itemMock = $this->createPartialMockWithReflection(
            Item::class,
            [
                'load',
                'getId',
                'setQty',
                'setOptions',
                'getBuyRequest',
                'mergeBuyRequest',
                'addToCart',
                'getProduct',
                'getWishlistId',
                'getProductId'
            ]
        );

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

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($itemId, $qty) {
                if ($arg1 == 'item') {
                    return $itemId;
                } elseif ($arg1 == 'qty') {
                    return $qty;
                }
            });

        $this->quantityProcessorMock->expects($this->once())
            ->method('process')
            ->with(1)
            ->willReturnArgument(0);

        $itemMock->expects($this->once())
            ->method('setQty')
            ->with(1)
            ->willReturnSelf();

        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->urlMock
            ->method('getUrl')
            ->willReturnCallback(function ($arg1, $arg2) use ($indexUrl, $configureUrl, $itemId, $productId) {
                if ($arg1 == '*/*' && is_null($arg2)) {
                    return $indexUrl;
                } elseif ($arg1 == '*/*/configure/' && $arg2['id'] == $itemId && $arg2['product_id'] == $productId) {
                    return $configureUrl;
                }
            });

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
     * @return void
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecuteWithEditQuantity(): void
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

        $itemMock = $this->createPartialMockWithReflection(
            Item::class,
            [
                'load',
                'getId',
                'setQty',
                'setOptions',
                'getBuyRequest',
                'mergeBuyRequest',
                'addToCart',
                'getProduct',
                'getWishlistId',
                'getProductId'
            ]
        );

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

        $this->requestMock
            ->method('getParam')
            ->willReturnCallback(function ($arg1, $arg2) use ($itemId, $qty) {
                if ($arg1 == 'item') {
                    return $itemId;
                } elseif ($arg1 == 'qty') {
                    return $qty;
                }
            });

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

        $itemMock->expects($this->once())
            ->method('getProductId')
            ->willReturn($productId);

        $this->urlMock
            ->method('getUrl')
            ->willReturnCallback(function ($arg1, $arg2) use ($indexUrl, $configureUrl, $itemId, $productId) {
                if ($arg1 == '*/*' && is_null($arg2)) {
                    return $indexUrl;
                } elseif ($arg1 == '*/*/configure/' && $arg2['id'] == $itemId && $arg2['product_id'] == $productId) {
                    return $configureUrl;
                }
            });

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
