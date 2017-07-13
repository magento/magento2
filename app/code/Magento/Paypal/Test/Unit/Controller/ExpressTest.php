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

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerSession;

    /** @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutSession;

    /** @var \Magento\Paypal\Model\Express\Checkout\Factory|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkoutFactory;

    /** @var \Magento\Framework\Session\Generic|\PHPUnit_Framework_MockObject_MockObject */
    protected $session;

    /** @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject */
    protected $quote;

    /** @var \Magento\Customer\Api\Data\CustomerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerData;

    /** @var \Magento\Paypal\Model\Express\Checkout|\PHPUnit_Framework_MockObject_MockObject */
    protected $checkout;

    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $redirect;

    /** @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $response;

    /** @var \Magento\Paypal\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $config;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var \Closure */
    protected $objectManagerCallback;

    protected function setUp()
    {
        $this->markTestIncomplete();
        $this->messageManager = $this->getMockForAbstractClass(\Magento\Framework\Message\ManagerInterface::class);
        $this->config = $this->createMock(\Magento\Paypal\Model\Config::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $this->quote->expects($this->any())
            ->method('hasItems')
            ->will($this->returnValue(true));
        $this->redirect = $this->getMockForAbstractClass(\Magento\Framework\App\Response\RedirectInterface::class);
        $this->response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $this->customerData = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->checkout = $this->createMock(\Magento\Paypal\Model\Express\Checkout::class);
        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->customerSession->expects($this->any())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($this->customerData));
        $this->checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->checkoutFactory = $this->createMock(\Magento\Paypal\Model\Express\Checkout\Factory::class);
        $this->checkoutFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->checkout));
        $this->checkoutSession->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quote));
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
            ->will($this->returnCallback(function ($className) {
                return call_user_func($this->objectManagerCallback, $className);
            }));
        $objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnCallback(function ($className) {
                return call_user_func($this->objectManagerCallback, $className);
            }));

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
