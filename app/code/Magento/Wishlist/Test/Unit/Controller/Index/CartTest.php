<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Controller\Index;

use Magento\Wishlist\Controller\Index\Cart;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CartTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Cart
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProviderMock;

    /**
     * @var \Magento\Wishlist\Model\LocaleQuantityProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quantityProcessorMock;

    /**
     * @var \Magento\Wishlist\Model\ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemFactoryMock;

    /**
     * @var \Magento\Checkout\Model\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutCartMock;

    /**
     * @var \Magento\Wishlist\Model\Item\OptionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionFactoryMock;

    /**
     * @var \Magento\Catalog\Helper\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productHelperMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    /**
     * @var \Magento\Wishlist\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Checkout\Helper\Cart|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartHelperMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultJsonMock;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->wishlistProviderMock = $this->getMockBuilder('Magento\Wishlist\Controller\WishlistProviderInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getWishlist'])
            ->getMockForAbstractClass();

        $this->quantityProcessorMock = $this->getMockBuilder('Magento\Wishlist\Model\LocaleQuantityProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemFactoryMock = $this->getMockBuilder('Magento\Wishlist\Model\ItemFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->checkoutCartMock = $this->getMockBuilder('Magento\Checkout\Model\Cart')
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getQuote', 'getShouldRedirectToCart', 'getCartUrl'])
            ->getMock();

        $this->optionFactoryMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\OptionFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->productHelperMock = $this->getMockBuilder('Magento\Catalog\Helper\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $this->escaperMock = $this->getMockBuilder('Magento\Framework\Escaper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperMock = $this->getMockBuilder('Magento\Wishlist\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getParams', 'getParam', 'isAjax'])
            ->getMockForAbstractClass();

        $this->redirectMock = $this->getMockBuilder('Magento\Framework\App\Response\RedirectInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManagerInterface')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['addSuccess'])
            ->getMockForAbstractClass();

        $this->urlMock = $this->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getUrl'])
            ->getMockForAbstractClass();
        $this->cartHelperMock = $this->getMockBuilder('Magento\Checkout\Helper\Cart')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultFactoryMock = $this->getMockBuilder('Magento\Framework\Controller\ResultFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultRedirectMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Redirect')
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonMock = $this->getMockBuilder('Magento\Framework\Controller\Result\Json')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock = $this->getMockBuilder('Magento\Framework\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->requestMock));
        $this->contextMock->expects($this->any())
            ->method('getRedirect')
            ->will($this->returnValue($this->redirectMock));
        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->will($this->returnValue($this->objectManagerMock));
        $this->contextMock->expects($this->any())
            ->method('getMessageManager')
            ->will($this->returnValue($this->messageManagerMock));
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

        $this->formKeyValidator = $this->getMockBuilder('Magento\Framework\Data\Form\FormKey\Validator')
            ->disableOriginalConstructor()
            ->getMock();

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
            $this->formKeyValidator
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

        $itemMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
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

        $itemMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
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

        $itemMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
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

        $wishlistMock = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
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

        $optionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $optionsMock = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Option\Collection')
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

        $buyRequestMock = $this->getMockBuilder('Magento\Framework\DataObject')
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

        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')
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

        $productMock = $this->getMockBuilder('Magento\Catalog\Model\Product')
            ->disableOriginalConstructor()
            ->getMock();

        $itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $productMock->expects($this->once())
            ->method('getName')
            ->willReturn($productName);

        $this->escaperMock->expects($this->once())
            ->method('escapeHtml')
            ->with($productName, null)
            ->willReturn($productName);

        $this->messageManagerMock->expects($this->once())
            ->method('addSuccess')
            ->with('You added '  . $productName . ' to your shopping cart.', null)
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

        $itemMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
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

        $wishlistMock = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
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

        $optionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $optionsMock = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Option\Collection')
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

        $buyRequestMock = $this->getMockBuilder('Magento\Framework\DataObject')
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
            ->method('addError')
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

        $itemMock = $this->getMockBuilder('Magento\Wishlist\Model\Item')
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

        $wishlistMock = $this->getMockBuilder('Magento\Wishlist\Model\Wishlist')
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

        $optionMock = $this->getMockBuilder('Magento\Wishlist\Model\Item\Option')
            ->disableOriginalConstructor()
            ->getMock();

        $this->optionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($optionMock);

        $optionsMock = $this->getMockBuilder('Magento\Wishlist\Model\ResourceModel\Item\Option\Collection')
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

        $buyRequestMock = $this->getMockBuilder('Magento\Framework\DataObject')
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
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('message')));

        $this->messageManagerMock->expects($this->once())
            ->method('addNotice')
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
