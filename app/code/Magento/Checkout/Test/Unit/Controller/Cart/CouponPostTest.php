<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Checkout\Controller\Cart\Index;
use Magento\Framework\Data\Form\FormKey\Validator;

/**
 * Test for \Magento\Checkout\Controller\Cart\CouponPost
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CouponPostTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Index
     */
    protected $controller;

    /**
     * @var \Magento\Checkout\Model\Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\App\Request\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $response;

    /**
     * @var \Magento\Quote\Model\Quote | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quote;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\Framework\Event\Manager | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cart;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $couponFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddress;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirect;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectFactory;

    /**
     * @var Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formKeyValidatorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $coupon;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->request->expects($this->any())->method('isPost')->willReturn(true);
        $this->response = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->quote = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            [
                'setCouponCode', 'getItemsCount', 'getShippingAddress', 'setCollectShippingRates', 'getCouponCode',
                'collectTotals', 'save'
            ],
            [],
            '',
            false
        );
        $this->eventManager = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);
        $this->checkoutSession = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);
        $this->coupon = $this->getMock(\Magento\SalesRule\Model\Coupon::class, ['load', 'getId'], [], '', false);

        $this->objectManagerMock = $this->getMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            ['get', 'escapeHtml'],
            [],
            '',
            false
        );

        $this->messageManager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMock(\Magento\Framework\App\Action\Context::class, [], [], '', false);
        $context->expects($this->once())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
        $context->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->request);
        $context->expects($this->once())
            ->method('getResponse')
            ->willReturn($this->response);
        $context->expects($this->once())
            ->method('getEventManager')
            ->willReturn($this->eventManager);
        $context->expects($this->once())
            ->method('getMessageManager')
            ->willReturn($this->messageManager);

        $this->redirectFactory =
            $this->getMock(\Magento\Framework\Controller\Result\RedirectFactory::class, [], [], '', false);
        $this->redirect = $this->getMock(\Magento\Store\App\Response\Redirect::class, [], [], '', false);

        $this->redirect->expects($this->any())
            ->method('getRefererUrl')
            ->willReturn(null);

        $context->expects($this->once())
            ->method('getRedirect')
            ->willReturn($this->redirect);

        $context->expects($this->once())
            ->method('getResultRedirectFactory')
            ->willReturn($this->redirectFactory);

        $this->cart = $this->getMockBuilder(\Magento\Checkout\Model\Cart::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->couponFactory = $this->getMockBuilder(\Magento\SalesRule\Model\CouponFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->quoteRepository = $this->getMock(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->shippingAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);
        $this->formKeyValidatorMock = $this->getMock(Validator::class, [], [], '', false);
        $this->formKeyValidatorMock->expects($this->once())->method('validate')->willReturn(true);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->controller = $objectManagerHelper->getObject(
            \Magento\Checkout\Controller\Cart\CouponPost::class,
            [
                'context' => $context,
                'checkoutSession' => $this->checkoutSession,
                'cart' => $this->cart,
                'couponFactory' => $this->couponFactory,
                'quoteRepository' => $this->quoteRepository,
                'formKeyValidator' => $this->formKeyValidatorMock,
            ]
        );
    }

    public function testExecuteWithEmptyCoupon()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['remove', null, 0],
                ['coupon_code', null, ''],
            ]
        );

        $this->cart->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->controller->execute();
    }

    public function testExecuteWithGoodCouponAndItems()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['remove', null, 0],
                ['coupon_code', null, 'CODE'],
            ]
        );

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->coupon);

        $this->coupon->expects($this->once())->method('getId')->willReturn(1);

        $this->quote->expects($this->any())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->quote->expects($this->any())
            ->method('setCollectShippingRates')
            ->with(true);

        $this->quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);

        $this->quote->expects($this->any())
            ->method('collectTotals')
            ->willReturn($this->quote);

        $this->quote->expects($this->any())
            ->method('setCouponCode')
            ->with('CODE')
            ->willReturnSelf();

        $this->quote->expects($this->any())
            ->method('getCouponCode')
            ->willReturn('CODE');

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithGoodCouponAndNoItems()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['remove', null, 0],
                ['coupon_code', null, 'CODE'],
            ]
        );

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $this->quote->expects($this->any())
            ->method('getItemsCount')
            ->willReturn(0);

        $this->coupon->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->coupon);

        $this->checkoutSession->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->any())
            ->method('setCouponCode')
            ->with('CODE')
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithBadCouponAndItems()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['remove', null, 0],
                ['coupon_code', null, 'CODE'],
            ]
        );

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $this->quote->expects($this->any())
            ->method('getItemsCount')
            ->willReturn(1);

        $this->quote->expects($this->any())
            ->method('setCollectShippingRates')
            ->with(true);

        $this->quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($this->shippingAddress);

        $this->quote->expects($this->any())
            ->method('setCouponCode')
            ->with('CODE')
            ->willReturnSelf();

        $this->quote->expects($this->any())
            ->method('collectTotals')
            ->willReturn($this->quote);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->coupon);

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithBadCouponAndNoItems()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['remove', null, 0],
                ['coupon_code', null, 'CODE'],
            ]
        );

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $this->quote->expects($this->any())
            ->method('getItemsCount')
            ->willReturn(0);

        $this->coupon->expects($this->once())
            ->method('getId')
            ->willReturn(0);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->coupon);

        $this->messageManager->expects($this->once())
            ->method('addErrorMessage')
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testCancelCoupon()
    {
        $this->request->expects($this->any())->method('getParam')->willReturnMap(
            [
                ['remove', null, 0],
                ['coupon_code', null, ''],
            ]
        );

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $this->cart->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->messageManager->expects($this->once())
            ->method('addSuccessMessage')
            ->with('You canceled the coupon code.');

        $this->controller->execute();
    }
}
