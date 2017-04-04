<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Test\Unit\Controller\Cart;

use Magento\Checkout\Controller\Cart\Index;

/**
 * Class IndexTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    private $redirect;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectFactory;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->request = $this->getMock(\Magento\Framework\App\Request\Http::class, [], [], '', false);
        $this->response = $this->getMock(\Magento\Framework\App\Response\Http::class, [], [], '', false);
        $this->quote = $this->getMock(
            \Magento\Quote\Model\Quote::class,
            [
                'setCouponCode',
                'getItemsCount',
                'getShippingAddress',
                'setCollectShippingRates',
                'getCouponCode',
                'collectTotals',
                'save'
            ],
            [],
            '',
            false
        );
        $this->eventManager = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);
        $this->checkoutSession = $this->getMock(\Magento\Checkout\Model\Session::class, [], [], '', false);

        $this->objectManagerMock = $this->getMock(
            \Magento\Framework\ObjectManager\ObjectManager::class,
            [
                'get', 'escapeHtml'
            ],
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

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->controller = $objectManagerHelper->getObject(
            \Magento\Checkout\Controller\Cart\CouponPost::class,
            [
                'context' => $context,
                'checkoutSession' => $this->checkoutSession,
                'cart' => $this->cart,
                'couponFactory' => $this->couponFactory,
                'quoteRepository' => $this->quoteRepository
            ]
        );
    }

    public function testExecuteWithEmptyCoupon()
    {
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('remove')
            ->willReturn(0);

        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('coupon_code')
            ->willReturn('');

        $this->cart->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->controller->execute();
    }

    public function testExecuteWithGoodCouponAndItems()
    {
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('remove')
            ->willReturn(0);

        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('coupon_code')
            ->willReturn('CODE');

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $coupon = $this->getMock(\Magento\SalesRule\Model\Coupon::class, [], [], '', false);
        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);
        $coupon->expects($this->once())->method('load')->willReturnSelf();
        $coupon->expects($this->once())->method('getId')->willReturn(1);
        $this->quote->expects($this->any())
            ->method('getItemsCount')
            ->willReturn(1);

        $shippingAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);

        $this->quote->expects($this->any())
            ->method('setCollectShippingRates')
            ->with(true);

        $this->quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

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
            ->method('addSuccess')
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithGoodCouponAndNoItems()
    {
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('remove')
            ->willReturn(0);

        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('coupon_code')
            ->willReturn('CODE');

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $this->quote->expects($this->any())
            ->method('getItemsCount')
            ->willReturn(0);

        $coupon = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);

        $coupon->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->checkoutSession->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->any())
            ->method('setCouponCode')
            ->with('CODE')
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithBadCouponAndItems()
    {
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('remove')
            ->willReturn(0);

        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('coupon_code')
            ->willReturn('');

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $this->quote->expects($this->any())
            ->method('getItemsCount')
            ->willReturn(1);

        $shippingAddress = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);

        $this->quote->expects($this->any())
            ->method('setCollectShippingRates')
            ->with(true);

        $this->quote->expects($this->any())
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $this->quote->expects($this->any())
            ->method('collectTotals')
            ->willReturn($this->quote);

        $this->quote->expects($this->any())
            ->method('setCouponCode')
            ->with('')
            ->willReturnSelf();

        $this->messageManager->expects($this->once())
            ->method('addSuccess')
            ->with('You canceled the coupon code.')
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithBadCouponAndNoItems()
    {
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('remove')
            ->willReturn(0);

        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('coupon_code')
            ->willReturn('CODE');

        $this->cart->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);

        $this->quote->expects($this->at(0))
            ->method('getCouponCode')
            ->willReturn('OLDCODE');

        $this->quote->expects($this->any())
            ->method('getItemsCount')
            ->willReturn(0);

        $coupon = $this->getMock(\Magento\Quote\Model\Quote\Address::class, [], [], '', false);

        $coupon->expects($this->once())
            ->method('getId')
            ->willReturn(0);

        $this->couponFactory->expects($this->once())
            ->method('create')
            ->willReturn($coupon);

        $this->messageManager->expects($this->once())
            ->method('addError')
            ->willReturnSelf();

        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->willReturnSelf();

        $this->controller->execute();
    }
}
