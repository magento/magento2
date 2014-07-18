<?php
/**
 *
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
namespace Magento\Paypal\Controller\Express;

class PlaceOrderTest extends \Magento\Paypal\Controller\ExpressTest
{
    protected $name = 'PlaceOrder';


    /**
     * @param bool $isGeneral
     * @dataProvider trueFalseDataProvider
     */
    public function testExecuteNonProcessableException($isGeneral)
    {
        if (!$isGeneral) {
            $this->request->expects($this->once())
                ->method('getPost')
                ->with('agreement', [])
                ->will($this->returnValue([]));
        }
        $this->_expectRedirect();
        $this->model->execute();
    }

    /**
     * @param string $path
     */
    protected function _expectRedirect($path = '*/*/review')
    {
        $this->redirect->expects($this->once())
            ->method('redirect')
            ->with($this->anything(), $path, []);
    }

    public function trueFalseDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @param int $code
     * @param null|string $paymentAction
     * @dataProvider executeProcessableExceptionDataProvider
     */
    public function testExecuteProcessableException($code, $paymentAction = null)
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
        $this->model->execute();
    }

    public function executeProcessableExceptionDataProvider()
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

    /**
     * @param int $code
     * @param null|string $paymentAction
     */
    protected function _expectErrorCodes($code, $paymentAction)
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
