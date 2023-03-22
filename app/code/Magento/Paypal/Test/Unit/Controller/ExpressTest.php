<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\Controller;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Session\Generic;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Express\Checkout;
use Magento\Paypal\Model\Express\Checkout\Factory;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ExpressTest extends TestCase
{
    /** @var Express */
    protected $model;

    /** @var string */
    protected $name = '';

    /** @var Session|MockObject */
    protected $customerSession;

    /** @var \Magento\Checkout\Model\Session|MockObject */
    protected $checkoutSession;

    /** @var Factory|MockObject */
    protected $checkoutFactory;

    /** @var Generic|MockObject */
    protected $session;

    /** @var Quote|MockObject */
    protected $quote;

    /** @var CustomerInterface|MockObject */
    protected $customerData;

    /** @var Checkout|MockObject */
    protected $checkout;

    /** @var RequestInterface|MockObject */
    protected $request;

    /** @var RedirectInterface|MockObject */
    protected $redirect;

    /** @var ResponseInterface|MockObject */
    protected $response;

    /** @var Config|MockObject */
    protected $config;

    /** @var ManagerInterface|MockObject */
    protected $messageManager;

    /** @var \Closure */
    protected $objectManagerCallback;

    protected function setUp(): void
    {
        $this->markTestSkipped();
        $this->messageManager = $this->getMockForAbstractClass(ManagerInterface::class);
        $this->config = $this->createMock(Config::class);
        $this->request = $this->createMock(Http::class);
        $this->quote = $this->createMock(Quote::class);
        $this->quote->expects($this->any())
            ->method('hasItems')
            ->willReturn(true);
        $this->redirect = $this->getMockForAbstractClass(RedirectInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->customerData = $this->getMockForAbstractClass(CustomerInterface::class);
        $this->checkout = $this->createMock(Checkout::class);
        $this->customerSession = $this->createMock(Session::class);
        $this->customerSession->expects($this->any())
            ->method('getCustomerDataObject')
            ->willReturn($this->customerData);
        $this->checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->checkoutFactory = $this->createMock(Factory::class);
        $this->checkoutFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->checkout);
        $this->checkoutSession->expects($this->any())
            ->method('getQuote')
            ->willReturn($this->quote);
        $this->session = $this->createMock(Generic::class);
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->objectManagerCallback = function ($className) {
            if ($className == Config::class) {
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
