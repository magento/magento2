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
class ExpressTest extends \PHPUnit_Framework_TestCase
{
    /** @var Express */
    protected $model;

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
            'Magento\Paypal\Controller\Express',
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

    /**
     * @param null|bool $buttonParam
     * @dataProvider startActionDataProvider
     */
    public function testStartAction($buttonParam)
    {
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('bml')
            ->will($this->returnValue($buttonParam));
        $this->checkout->expects($this->once())
            ->method('setIsBml')
            ->with((bool)$buttonParam);

        $this->request->expects($this->at(2))
            ->method('getParam')
            ->with(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_BUTTON)
            ->will($this->returnValue($buttonParam));
        $this->customerData->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->checkout->expects($this->once())
            ->method('start')
            ->with($this->anything(), $this->anything(), (bool)$buttonParam);
        $this->model->startAction();
    }

    public function startActionDataProvider()
    {
        return [['1'], [null]];
    }

    public function testReturnActionAuthorizationRetrial()
    {
        $this->request->expects($this->once())
            ->method('getParam')
            ->with('retry_authorization')
            ->will($this->returnValue('true'));
        $this->checkoutSession->expects($this->once())
            ->method('__call')
            ->with('getPaypalTransactionData')
            ->will($this->returnValue(['any array']));
        $this->_expectForwardPlaceOrder();
        $this->model->returnAction();
    }

    /**
     * @param bool $canSkipOrderReviewStep
     * @dataProvider trueFalseDataProvider
     */
    public function testReturnAction($canSkipOrderReviewStep)
    {
        $this->checkoutSession->expects($this->at(0))
            ->method('__call')
            ->with('unsPaypalTransactionData');
        $this->checkout->expects($this->once())
            ->method('canSkipOrderReviewStep')
            ->will($this->returnValue($canSkipOrderReviewStep));
        if ($canSkipOrderReviewStep) {
            $this->_expectForwardPlaceOrder();
        } else {
            $this->_expectRedirect();
        }
        $this->model->returnAction();
    }

    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @param bool $isGeneral
     * @dataProvider trueFalseDataProvider
     */
    public function testPlaceOrderActionNonProcessableException($isGeneral)
    {
        if (!$isGeneral) {
            $this->request->expects($this->once())
                ->method('getPost')
                ->with('agreement', [])
                ->will($this->returnValue([]));
        }
        $this->_expectRedirect();
        $this->model->placeOrderAction();
    }

    /**
     * @param int $code
     * @param null|string $paymentAction
     * @dataProvider placeOrderActionProcessableExceptionDataProvider
     */
    public function testPlaceOrderActionProcessableException($code, $paymentAction = null)
    {
        $this->request->expects($this->once())
            ->method('getPost')
            ->with('agreement', [])
            ->will($this->returnValue([]));
        $oldCallback = &$this->objectManagerCallback;
        $this->objectManagerCallback = function ($className) use ($code, $oldCallback) {
            $instance = call_user_func($oldCallback, $className);
            if ($className == 'Magento\Checkout\Model\Agreements\AgreementsValidator') {
                $exception = $this->getMock(
                    'Magento\Paypal\Model\Api\ProcessableException',
                    ['getUserMessage'],
                    ['message', $code]
                );
                $exception->expects($this->any())
                    ->method('getUserMessage')
                    ->will($this->returnValue('User Message'));
                $instance->expects($this->once())
                    ->method('isValid')
                    ->will($this->throwException($exception));
            }
            return $instance;
        };
        if (isset($paymentAction)) {
            $this->config->expects($this->once())
                ->method('getPaymentAction')
                ->will($this->returnValue($paymentAction));
        }
        $this->_expectErrorCodes($code, $paymentAction);
        $this->model->placeOrderAction();
    }

    public function placeOrderActionProcessableExceptionDataProvider()
    {
        return [
            [\Magento\Paypal\Model\Api\ProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED],
            [\Magento\Paypal\Model\Api\ProcessableException::API_TRANSACTION_EXPIRED],
            [\Magento\Paypal\Model\Api\ProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL],
            [
                \Magento\Paypal\Model\Api\ProcessableException::API_UNABLE_TRANSACTION_COMPLETE,
                \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER
            ],
            [\Magento\Paypal\Model\Api\ProcessableException::API_UNABLE_TRANSACTION_COMPLETE, 'other'],
            [999999],
        ];
    }

    private function _expectForwardPlaceOrder()
    {
        $this->request->expects($this->once())
            ->method('setActionName')
            ->with('placeOrder');
        $this->request->expects($this->once())
            ->method('setDispatched')
            ->with(false);
    }

    /**
     * @param string $path
     */
    private function _expectRedirect($path = '*/*/review')
    {
        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with($this->anything(), $path, []);
    }

    /**
     * @param int $code
     * @param null|string $paymentAction
     */
    private function _expectErrorCodes($code, $paymentAction)
    {
        $redirectUrl = 'redirect by test';
        if (in_array(
            $code,
            [
                \Magento\Paypal\Model\Api\ProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED,
                \Magento\Paypal\Model\Api\ProcessableException::API_TRANSACTION_EXPIRED,
            ]
        )
        ) {
            $payment = new \Magento\Framework\Object(['checkout_redirect_url' => $redirectUrl]);
            $this->quote->expects($this->once())
                ->method('getPayment')
                ->will($this->returnValue($payment));
        }
        if ($code == \Magento\Paypal\Model\Api\ProcessableException::API_UNABLE_TRANSACTION_COMPLETE
            && $paymentAction == \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER
        ) {
            $this->config->expects($this->once())
                ->method('getExpressCheckoutOrderUrl')
                ->will($this->returnValue($redirectUrl));
        }
        if ($code == \Magento\Paypal\Model\Api\ProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL
            || $code == \Magento\Paypal\Model\Api\ProcessableException::API_UNABLE_TRANSACTION_COMPLETE
            && $paymentAction != \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER
        ) {
            $this->config->expects($this->once())
                ->method('getExpressCheckoutStartUrl')
                ->will($this->returnValue($redirectUrl));
            $this->request->expects($this->once())
                ->method('getParam')
                ->with('token');
        }
        if (in_array(
            $code,
            [
                \Magento\Paypal\Model\Api\ProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED,
                \Magento\Paypal\Model\Api\ProcessableException::API_TRANSACTION_EXPIRED,
                \Magento\Paypal\Model\Api\ProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL,
                \Magento\Paypal\Model\Api\ProcessableException::API_UNABLE_TRANSACTION_COMPLETE,
            ]
        )
        ) {
            $this->response->expects($this->once())
                ->method('setRedirect')
                ->with($redirectUrl);
        } else {
            $this->messageManager->expects($this->once())
                ->method('addError')
                ->with('User Message');
            $this->_expectRedirect('checkout/cart');
        }
    }
}
