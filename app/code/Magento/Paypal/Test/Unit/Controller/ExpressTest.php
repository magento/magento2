<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Test\Unit\Controller;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ExpressTest extends \PHPUnit\Framework\TestCase
{
    /** @var Express */
    protected $model;

    protected $name = '';

    /** @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerSession;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit\Framework\MockObject\MockObject */
    protected $checkoutSession;

    /** @var \Magento\Paypal\Model\Express\Checkout\Factory|\PHPUnit\Framework\MockObject\MockObject */
    protected $checkoutFactory;

    /** @var \Magento\Framework\Session\Generic|\PHPUnit\Framework\MockObject\MockObject */
    protected $session;

    /** @var \Magento\Quote\Model\Quote|\PHPUnit\Framework\MockObject\MockObject */
    protected $quote;

    /** @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $customerData;

    /** @var \Magento\Paypal\Model\Express\Checkout|\PHPUnit\Framework\MockObject\MockObject */
    protected $checkout;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $redirect;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $response;

    /** @var \Magento\Paypal\Model\Config|\PHPUnit\Framework\MockObject\MockObject */
    protected $config;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $messageManager;

    /** @var \Closure */
    protected $objectManagerCallback;

    protected function setUp(): void
    {
        $this->markTestIncomplete();
        $this->messageManager = $this->getMockForAbstractClass(\Magento\Framework\Message\ManagerInterface::class);
        $this->config = $this->createMock(\Magento\Paypal\Model\Config::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->quote->expects($this->any())
            ->method('hasItems')
            ->willReturn(true);
        $this->redirect = $this->getMockForAbstractClass(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->customerData = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->checkout = $this->createMock(\Magento\Paypal\Model\Express\Checkout::class);
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->customerSession->expects($this->any())
            ->method('getCustomerDataObject')
            ->willReturn($this->customerData);
        $this->checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->checkoutFactory = $this->createMock(\Magento\Paypal\Model\Express\Checkout\Factory::class);
        $this->checkoutFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->checkout);
        $this->checkoutSession->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);
        $this->session = $this->createMock(\Magento\Framework\Session\Generic::class);
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->objectManagerCallback = function ($className) {
            if ($className == \Magento\Paypal\Model\Config::class) {
                return $this->config;
            }
            return $this->createMock($className);
        };
        $objectManager->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($className) {
                return call_user_func($this->objectManagerCallback, $className);
            });
        $objectManager->expects($this->any())
            ->method('create')
            ->willReturnCallback(function ($className) {
                return call_user_func($this->objectManagerCallback, $className);
            });

        $helper = new ObjectManagerHelper($this);
        $this->model = $helper->getObject(
            '\\Magento\\Paypal\\Controller\\Express\\' . $this->name,
            [
                'messageManager' => $this->messageManager,
                'response' => $this->response,
                'redirect' => $this->redirect,
                'request' => $this->request,
                'customerSession' => $this->customerSession,
                'checkoutSession' => $this->checkoutSession,
                'checkoutFactory' => $this->checkoutFactory,
                'paypalSession' => $this->session,
                'objectManager' => $objectManager,
            ]
        );
    }
}
