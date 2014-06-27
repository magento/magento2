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
namespace Magento\Paypal\Controller\Express;

use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\App\Action\Action as AppAction;
use Magento\Checkout\Controller\Express\RedirectLoginInterface;
use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;

/**
 * Abstract Express Checkout Controller
 */
abstract class AbstractExpress extends AppAction implements RedirectLoginInterface
{
    /**
     * @var \Magento\Paypal\Model\Express\Checkout
     */
    protected $_checkout;

    /**
     * Internal cache of checkout models
     *
     * @var array
     */
    protected $_checkoutTypes = array();

    /**
     * @var \Magento\Paypal\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Sales\Model\Quote
     */
    protected $_quote = false;

    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType;

    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod;

    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_checkoutType;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Paypal\Model\Express\Checkout\Factory
     */
    protected $_checkoutFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_paypalSession;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory
     * @param \Magento\Framework\Session\Generic $paypalSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\Express\Checkout\Factory $checkoutFactory,
        \Magento\Framework\Session\Generic $paypalSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_quoteFactory = $quoteFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_checkoutFactory = $checkoutFactory;
        $this->_paypalSession = $paypalSession;
        parent::__construct($context);
        $parameters = array('params' => array($this->_configMethod));
        $this->_config = $this->_objectManager->create($this->_configType, $parameters);
    }

    /**
     * Start Express Checkout by requesting initial token and dispatching customer to PayPal
     *
     * @return void
     */
    public function startAction()
    {
        try {
            $this->_initCheckout();

            if ($this->_getQuote()->getIsMultiShipping()) {
                $this->_getQuote()->setIsMultiShipping(false);
                $this->_getQuote()->removeAllAddresses();
            }

            $customerData = $this->_customerSession->getCustomerDataObject();
            $quoteCheckoutMethod = $this->_getQuote()->getCheckoutMethod();
            if ($customerData->getId()) {
                $this->_checkout->setCustomerWithAddressChange(
                    $customerData,
                    $this->_getQuote()->getBillingAddress(),
                    $this->_getQuote()->getShippingAddress()
                );
            } elseif ((!$quoteCheckoutMethod || $quoteCheckoutMethod != Onepage::METHOD_REGISTER)
                && !$this->_objectManager->get('Magento\Checkout\Helper\Data')->isAllowedGuestCheckout(
                    $this->_getQuote(),
                    $this->_getQuote()->getStoreId()
                )
            ) {

                $this->messageManager->addNotice(
                    __('To proceed to Checkout, please log in using your email address.')
                );

                $this->_objectManager->get('Magento\Checkout\Helper\ExpressRedirect')->redirectLogin($this);
                $this->_customerSession->setBeforeAuthUrl($this->_url->getUrl('*/*/*', array('_current' => true)));

                return;
            }

            // billing agreement
            $isBaRequested = (bool)$this->getRequest()
                ->getParam(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
            if ($customerData->getId()) {
                $this->_checkout->setIsBillingAgreementRequested($isBaRequested);
            }

            // Bill Me Later
            $this->_checkout->setIsBml((bool)$this->getRequest()->getParam('bml'));

            // giropay
            $this->_checkout->prepareGiropayUrls(
                $this->_url->getUrl('checkout/onepage/success'),
                $this->_url->getUrl('paypal/express/cancel'),
                $this->_url->getUrl('checkout/onepage/success')
            );

            $button = (bool)$this->getRequest()->getParam(\Magento\Paypal\Model\Express\Checkout::PAYMENT_INFO_BUTTON);
            $token = $this->_checkout->start(
                $this->_url->getUrl('*/*/return'),
                $this->_url->getUrl('*/*/cancel'),
                $button
            );
            $url = $this->_checkout->getRedirectUrl();
            if ($token && $url) {
                $this->_initToken($token);
                $this->getResponse()->setRedirect($url);
                return;
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t start Express Checkout.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Return shipping options items for shipping address from request
     *
     * @return void
     */
    public function shippingOptionsCallbackAction()
    {
        try {
            $quoteId = $this->getRequest()->getParam('quote_id');
            $this->_quote = $this->_quoteFactory->create()->load($quoteId);
            $this->_initCheckout();
            $response = $this->_checkout->getShippingOptionsCallbackResponse($this->getRequest()->getParams());
            $this->getResponse()->setBody($response);
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }

    /**
     * Cancel Express Checkout
     *
     * @return void
     */
    public function cancelAction()
    {
        try {
            $this->_initToken(false);
            // TODO verify if this logic of order cancellation is deprecated
            // if there is an order - cancel it
            $orderId = $this->_getCheckoutSession()->getLastOrderId();
            /** @var \Magento\Sales\Model\Order $order */
            $order = $orderId ? $this->_orderFactory->create()->load($orderId) : false;
            if ($order && $order->getId() && $order->getQuoteId() == $this->_getCheckoutSession()->getQuoteId()) {
                $order->cancel()->save();
                $this->_getCheckoutSession()
                    ->unsLastQuoteId()
                    ->unsLastSuccessQuoteId()
                    ->unsLastOrderId()
                    ->unsLastRealOrderId();
                $this->messageManager->addSuccess(__('Express Checkout and Order have been canceled.'));
            } else {
                $this->messageManager->addSuccess(__('Express Checkout has been canceled.'));
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('Unable to cancel Express Checkout'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }

        $this->_redirect('checkout/cart');
    }

    /**
     * Return from PayPal and dispatch customer to order review page
     *
     * @return void
     */
    public function returnAction()
    {
        if ($this->getRequest()->getParam('retry_authorization') == 'true'
            && is_array($this->_getCheckoutSession()->getPaypalTransactionData())
        ) {
            $this->_forward('placeOrder');
            return;
        }
        try {
            $this->_getCheckoutSession()->unsPaypalTransactionData();
            $this->_initCheckout();
            $this->_checkout->returnFromPaypal($this->_initToken());
            if ($this->_checkout->canSkipOrderReviewStep()) {
                $this->_forward('placeOrder');
            } else {
                $this->_redirect('*/*/review');
            }
            return;
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t process Express Checkout approval.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Review order after returning from PayPal
     *
     * @return void
     */
    public function reviewAction()
    {
        try {
            $this->_initCheckout();
            $this->_checkout->prepareOrderReview($this->_initToken());
            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();
            $reviewBlock = $this->_view->getLayout()->getBlock('paypal.express.review');
            $reviewBlock->setQuote($this->_getQuote());
            $reviewBlock->getChildBlock('details')->setQuote($this->_getQuote());
            if ($reviewBlock->getChildBlock('shipping_method')) {
                $reviewBlock->getChildBlock('shipping_method')->setQuote($this->_getQuote());
            }
            $this->_view->renderLayout();
            return;
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t initialize Express Checkout review.')
            );
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->_redirect('checkout/cart');
    }

    /**
     * Dispatch customer back to PayPal for editing payment information
     *
     * @return void
     */
    public function editAction()
    {
        try {
            $this->getResponse()->setRedirect($this->_config->getExpressCheckoutEditUrl($this->_initToken()));
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/review');
        }
    }

    /**
     * Update shipping method (combined action for ajax and regular request)
     *
     * @return void
     */
    public function saveShippingMethodAction()
    {
        try {
            $isAjax = $this->getRequest()->getParam('isAjax');
            $this->_initCheckout();
            $this->_checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
            if ($isAjax) {
                $this->_view->loadLayout('paypal_express_review_details');
                $this->getResponse()->setBody(
                    $this->_view->getLayout()->getBlock('root')->setQuote($this->_getQuote())->toHtml()
                );
                return;
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t update shipping method.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        if ($isAjax) {
            $this->getResponse()->setBody(
                '<script type="text/javascript">window.location.href = '
                . $this->_url->getUrl('*/*/review')
                . ';</script>'
            );
        } else {
            $this->_redirect('*/*/review');
        }
    }

    /**
     * Update Order (combined action for ajax and regular request)
     *
     * @return void
     */
    public function updateShippingMethodsAction()
    {
        try {
            $this->_initCheckout();
            $this->_checkout->prepareOrderReview($this->_initToken());
            $this->_view->loadLayout('paypal_express_review');

            $this->getResponse()->setBody(
                $this->_view
                    ->getLayout()
                    ->getBlock('express.review.shipping.method')
                    ->setQuote($this->_getQuote())
                    ->toHtml()
            );
            return;
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t update shipping method.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
        $this->getResponse()->setBody(
            '<script type="text/javascript">window.location.href = ' . $this->_url->getUrl('*/*/review') . ';</script>'
        );
    }

    /**
     * Submit the order
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    public function placeOrderAction()
    {
        try {
            $agreementsValidator = $this->_objectManager->get('Magento\Checkout\Model\Agreements\AgreementsValidator');
            if (!$agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', [])))) {
                throw new \Magento\Framework\Model\Exception(
                    __('Please agree to all the terms and conditions before placing the order.')
                );
            }

            $this->_initCheckout();
            $this->_checkout->place($this->_initToken());

            // prepare session to success or cancellation page
            $this->_getCheckoutSession()->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $this->_getCheckoutSession()->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $this->_getCheckoutSession()->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
            }

            $this->_eventManager->dispatch(
                'paypal_express_place_order_success',
                [
                    'order' => $order,
                    'quote' => $this->_getQuote()
                ]
            );

            // redirect if PayPal specified some URL (for example, to Giropay bank)
            $url = $this->_checkout->getRedirectUrl();
            if ($url) {
                $this->getResponse()->setRedirect($url);
                return;
            }
            $this->_initToken(false); // no need in token anymore
            $this->_redirect('checkout/onepage/success');
            return;
        } catch (ApiProcessableException $e) {
            $this->_processPaypalApiError($e);
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/review');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t place the order.'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->_redirect('*/*/review');
        }
    }

    /**
     * Process PayPal API's processable errors
     *
     * @param \Magento\Paypal\Model\Api\ProcessableException $exception
     * @return void
     */
    protected function _processPaypalApiError($exception)
    {
        switch ($exception->getCode()) {
            case ApiProcessableException::API_MAX_PAYMENT_ATTEMPTS_EXCEEDED:
            case ApiProcessableException::API_TRANSACTION_EXPIRED:
                $this->getResponse()->setRedirect(
                    $this->_getQuote()->getPayment()->getCheckoutRedirectUrl()
                );
                break;
            case ApiProcessableException::API_DO_EXPRESS_CHECKOUT_FAIL:
                $this->_redirectSameToken();
                break;
            case ApiProcessableException::API_UNABLE_TRANSACTION_COMPLETE:
                if ($this->_config->getPaymentAction() == \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER) {
                    $paypalTransactionData = $this->_getCheckoutSession()->getPaypalTransactionData();
                    $this->getResponse()->setRedirect(
                        $this->_config->getExpressCheckoutOrderUrl($paypalTransactionData['transaction_id'])
                    );
                } else {
                    $this->_redirectSameToken();
                }
                break;
            default:
                $this->_redirectToCartAndShowError($exception->getUserMessage());
                break;
        }
    }

    /**
     * Redirect customer back to PayPal with the same token
     *
     * @return void
     */
    protected function _redirectSameToken()
    {
        $token = $this->_initToken();
        $this->getResponse()->setRedirect(
            $this->_config->getExpressCheckoutStartUrl($token)
        );
    }

    /**
     * Redirect customer to shopping cart and show error message
     *
     * @param string $errorMessage
     * @return void
     */
    protected function _redirectToCartAndShowError($errorMessage)
    {
        $this->messageManager->addError($errorMessage);
        $this->_redirect('checkout/cart');
    }

    /**
     * Instantiate quote and checkout
     *
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initCheckout()
    {
        $quote = $this->_getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
            throw new \Magento\Framework\Model\Exception(__('We can\'t initialize Express Checkout.'));
        }
        if (!isset($this->_checkoutTypes[$this->_checkoutType])) {
            $parameters = array(
                'params' => array(
                    'quote' => $quote,
                    'config' => $this->_config,
                ),
            );
            $this->_checkoutTypes[$this->_checkoutType] = $this->_checkoutFactory
                ->create($this->_checkoutType, $parameters);
        }
        $this->_checkout = $this->_checkoutTypes[$this->_checkoutType];
    }

    /**
     * Search for proper checkout token in request or session or (un)set specified one
     * Combined getter/setter
     *
     * @param string|null $setToken
     * @return \Magento\Paypal\Controller\Express|string
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _initToken($setToken = null)
    {
        if (null !== $setToken) {
            if (false === $setToken) {
                // security measure for avoid unsetting token twice
                if (!$this->_getSession()->getExpressCheckoutToken()) {
                    throw new \Magento\Framework\Model\Exception(__('PayPal Express Checkout Token does not exist.'));
                }
                $this->_getSession()->unsExpressCheckoutToken();
            } else {
                $this->_getSession()->setExpressCheckoutToken($setToken);
            }
            return $this;
        }
        $setToken = $this->getRequest()->getParam('token');
        if ($setToken) {
            if ($setToken !== $this->_getSession()->getExpressCheckoutToken()) {
                throw new \Magento\Framework\Model\Exception(__('A wrong PayPal Express Checkout Token is specified.'));
            }
        } else {
            $setToken = $this->_getSession()->getExpressCheckoutToken();
        }
        return $setToken;
    }

    /**
     * PayPal session instance getter
     *
     * @return \Magento\Framework\Session\Generic
     */
    private function _getSession()
    {
        return $this->_paypalSession;
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    private function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Sales\Model\Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Returns before_auth_url redirect parameter for customer session
     * @return null
     */
    public function getCustomerBeforeAuthUrl()
    {
        return;
    }

    /**
     * Returns a list of action flags [flag_key] => boolean
     * @return array
     */
    public function getActionFlagList()
    {
        return array();
    }

    /**
     * Returns login url parameter for redirect
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_objectManager->get('Magento\Customer\Helper\Data')->getLoginUrl();
    }

    /**
     * Returns action name which requires redirect
     * @return string
     */
    public function getRedirectActionName()
    {
        return 'start';
    }
}
