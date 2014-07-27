<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Paypal\Controller;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class ExpressTest extends \PHPUnit_Framework_TestCase
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

    /** @var \Magento\Sales\Model\Quote|\PHPUnit_Framework_MockObject_MockObject */
    protected $quote;

    /** @var \Magento\Customer\Service\V1\Data\Customer|\PHPUnit_Framework_MockObject_MockObject */
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
        $this->messageManager = $this->getMockForAbstractClass('Magento\Framework\Message\ManagerInterface');
        $this->config = $this->getMock('Magento\Paypal\Model\Config', [], [], '', false);
        $this->request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $this->quote = $this->getMock('Magento\Sales\Model\Quote', [], [], '', false);
        $this->quote->expects($this->any())
            ->method('hasItems')
            ->will($this->returnValue(true));
        $this->redirect = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');
        $this->response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $this->customerData = $this->getMock('Magento\Customer\Service\V1\Data\Customer', [], [], '', false);
        $this->checkout = $this->getMock('Magento\Paypal\Model\Express\Checkout', [], [], '', false);
        $this->customerSession = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->customerSession->expects($this->any())
            ->method('getCustomerDataObject')
            ->will($this->returnValue($this->customerData));
        $this->checkoutSession = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->checkoutFactory = $this->getMock('Magento\Paypal\Model\Express\Checkout\Factory', [], [], '', false);
        $this->checkoutFactory->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->checkout));
        $this->checkoutSession->expects($this->any())
            ->method('getQuote')
            ->will($this->returnValue($this->quote));
        $this->session = $this->getMock('Magento\Framework\Session\Generic', [], [], '', false);
        $objectManager = $this->getMock('Magento\Framework\ObjectManager', [], [], '', false);
        $this->objectManagerCallback = function ($className) {
            if ($className == 'Magento\Paypal\Model\Config') {
                return $this->config;
            }
            return $this->getMock($className, [], [], '', false);
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
            '\\Magento\\\Paypal\\Controller\\Express\\' . $this->name,
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
