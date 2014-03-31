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
namespace Magento\Multishipping\Controller;

use Magento\App\RequestInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface as CustomerAccountService;
use Magento\Customer\Service\V1\CustomerMetadataServiceInterface as CustomerMetadataService;

/**
 * Multishipping checkout controller
 */
class Checkout extends \Magento\Checkout\Controller\Action implements
    \Magento\Checkout\Controller\Express\RedirectLoginInterface
{
    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerAccountService $customerAccountService
     * @param CustomerMetadataService $customerMetadataService
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerAccountService $customerAccountService,
        CustomerMetadataService $customerMetadataService
    ) {
        parent::__construct($context, $customerSession, $customerAccountService, $customerMetadataService);
    }

    /**
     * Retrieve checkout model
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Multishipping\Model\Checkout\Type\Multishipping');
    }

    /**
     * Retrieve checkout state model
     *
     * @return \Magento\Multishipping\Model\Checkout\Type\Multishipping\State
     */
    protected function _getState()
    {
        return $this->_objectManager->get('Magento\Multishipping\Model\Checkout\Type\Multishipping\State');
    }

    /**
     * Retrieve checkout url helper
     *
     * @return \Magento\Multishipping\Helper\Url
     */
    protected function _getHelper()
    {
        return $this->_objectManager->get('Magento\Multishipping\Helper\Url');
    }

    /**
     * Retrieve checkout session
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_request = $request;
        if ($this->_actionFlag->get('', 'redirectLogin')) {
            return parent::dispatch($request);
        }

        $action = $request->getActionName();

        $checkoutSessionQuote = $this->_getCheckoutSession()->getQuote();
        /**
         * Catch index action call to set some flags before checkout/type_multishipping model initialization
         */
        if ($action == 'index') {
            $checkoutSessionQuote->setIsMultiShipping(true);
            $this->_getCheckoutSession()->setCheckoutState(\Magento\Checkout\Model\Session::CHECKOUT_STATE_BEGIN);
        } elseif (!$checkoutSessionQuote->getIsMultiShipping() && !in_array(
            $action,
            array('login', 'register', 'success')
        )
        ) {
            $this->_redirect('*/*/index');
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return parent::dispatch($request);
        }

        if (!in_array($action, array('login', 'register'))) {
            $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
            if (!$customerSession->authenticate($this, $this->_getHelper()->getMSLoginUrl())) {
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            }

            if (!$this->_objectManager->get('Magento\Multishipping\Helper\Data')->isMultishippingCheckoutAvailable()) {
                $error = $this->_getCheckout()->getMinimumAmountError();
                $this->messageManager->addError($error);
                $this->getResponse()->setRedirect($this->_getHelper()->getCartUrl());
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                return parent::dispatch($request);
            }
        }

        if (!$this->_preDispatchValidateCustomer()) {
            return $this->getResponse();
        }

        if ($this->_getCheckoutSession()->getCartWasUpdated(
            true
        ) && !in_array(
            $action,
            array('index', 'login', 'register', 'addresses', 'success')
        )
        ) {
            $this->getResponse()->setRedirect($this->_getHelper()->getCartUrl());
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return parent::dispatch($request);
        }

        if ($action == 'success' && $this->_getCheckout()->getCheckoutSession()->getDisplaySuccess(true)) {
            return parent::dispatch($request);
        }

        $quote = $this->_getCheckout()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || $quote->isVirtual()) {
            $this->getResponse()->setRedirect($this->_getHelper()->getCartUrl());
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Index action of Multishipping checkout
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_getCheckoutSession()->setCartWasUpdated(false);
        $this->_redirect('*/*/addresses');
    }

    /**
     * Multishipping checkout login page
     *
     * @return void
     */
    public function loginAction()
    {
        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();

        // set account create url
        $loginForm = $this->_view->getLayout()->getBlock('customer.new');
        if ($loginForm) {
            $loginForm->setCreateAccountUrl($this->_getHelper()->getMSRegisterUrl());
        }
        $this->_view->renderLayout();
    }

    /**
     * Multishipping checkout login page
     *
     * @return void
     */
    public function registerAction()
    {
        if ($this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()) {
            $this->getResponse()->setRedirect($this->_getHelper()->getMSCheckoutUrl());
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();

        $registerForm = $this->_view->getLayout()->getBlock('customer_form_register');
        if ($registerForm) {
            $registerForm->setShowAddressFields(
                true
            )->setBackUrl(
                $this->_getHelper()->getMSLoginUrl()
            )->setSuccessUrl(
                $this->_getHelper()->getMSShippingAddressSavedUrl()
            )->setErrorUrl(
                $this->_url->getCurrentUrl()
            );
        }

        $this->_view->renderLayout();
    }

    /**
     * Multishipping checkout select address page
     *
     * @return void
     */
    public function addressesAction()
    {
        // If customer do not have addresses
        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/checkout_address/newShipping');
            return;
        }

        $this->_getState()->unsCompleteStep(State::STEP_SHIPPING);

        $this->_getState()->setActiveStep(State::STEP_SELECT_ADDRESSES);
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $message = $this->_getCheckout()->getMinimumAmountDescription();
            $this->messageManager->addNotice($message);
        }
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Multishipping checkout process posted addresses
     *
     * @return void
     */
    public function addressesPostAction()
    {
        if (!$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $this->_redirect('*/checkout_address/newShipping');
            return;
        }
        try {
            if ($this->getRequest()->getParam('continue', false)) {
                $this->_getCheckout()->setCollectRatesFlag(true);
                $this->_getState()->setActiveStep(State::STEP_SHIPPING);
                $this->_getState()->setCompleteStep(State::STEP_SELECT_ADDRESSES);
                $this->_redirect('*/*/shipping');
            } elseif ($this->getRequest()->getParam('new_address')) {
                $this->_redirect('*/checkout_address/newShipping');
            } else {
                $this->_redirect('*/*/addresses');
            }
            if ($shipToInfo = $this->getRequest()->getPost('ship')) {
                $this->_getCheckout()->setShippingItemsInformation($shipToInfo);
            }
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/addresses');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Data saving problem'));
            $this->_redirect('*/*/addresses');
        }
    }

    /**
     * @return void
     */
    public function backToAddressesAction()
    {
        $this->_getState()->setActiveStep(State::STEP_SELECT_ADDRESSES);
        $this->_getState()->unsCompleteStep(State::STEP_SHIPPING);
        $this->_redirect('*/*/addresses');
    }

    /**
     * Multishipping checkout remove item action
     *
     * @return void
     */
    public function removeItemAction()
    {
        $itemId = $this->getRequest()->getParam('id');
        $addressId = $this->getRequest()->getParam('address');
        if ($addressId && $itemId) {
            $this->_getCheckout()->setCollectRatesFlag(true);
            $this->_getCheckout()->removeAddressItem($addressId, $itemId);
        }
        $this->_redirect('*/*/addresses');
    }

    /**
     * Validate minimum amount
     *
     * @return bool
     */
    protected function _validateMinimumAmount()
    {
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $error = $this->_getCheckout()->getMinimumAmountError();
            $this->messageManager->addError($error);
            $this->_forward('backToAddresses');
            return false;
        }
        return true;
    }

    /**
     * Multishipping checkout shipping information page
     *
     * @return  ResponseInterface|void
     */
    public function shippingAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_getState()->getCompleteStep(State::STEP_SELECT_ADDRESSES)) {
            return $this->_redirect('*/*/addresses');
        }

        $this->_getState()->setActiveStep(State::STEP_SHIPPING);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function backToShippingAction()
    {
        $this->_getState()->setActiveStep(State::STEP_SHIPPING);
        $this->_getState()->unsCompleteStep(State::STEP_BILLING);
        $this->_redirect('*/*/shipping');
    }

    /**
     * @return void
     */
    public function shippingPostAction()
    {
        $shippingMethods = $this->getRequest()->getPost('shipping_method');
        try {
            $this->_eventManager->dispatch(
                'checkout_controller_multishipping_shipping_post',
                array('request' => $this->getRequest(), 'quote' => $this->_getCheckout()->getQuote())
            );
            $this->_getCheckout()->setShippingMethods($shippingMethods);
            $this->_getState()->setActiveStep(State::STEP_BILLING);
            $this->_getState()->setCompleteStep(State::STEP_SHIPPING);
            $this->_redirect('*/*/billing');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/shipping');
        }
    }

    /**
     * Multishipping checkout billing information page
     *
     * @return void|ResponseInterface
     */
    public function billingAction()
    {
        if (!$this->_validateBilling()) {
            return;
        }

        if (!$this->_validateMinimumAmount()) {
            return;
        }

        if (!$this->_getState()->getCompleteStep(State::STEP_SHIPPING)) {
            return $this->_redirect('*/*/shipping');
        }

        $this->_getState()->setActiveStep(State::STEP_BILLING);
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Validation of selecting of billing address
     *
     * @return boolean
     */
    protected function _validateBilling()
    {
        if (!$this->_getCheckout()->getQuote()->getBillingAddress()->getFirstname()) {
            $this->_redirect('*/checkout_address/selectBilling');
            return false;
        }
        return true;
    }

    /**
     * Back to billing action
     *
     * @return void
     */
    public function backToBillingAction()
    {
        $this->_getState()->setActiveStep(State::STEP_BILLING);
        $this->_getState()->unsCompleteStep(State::STEP_OVERVIEW);
        $this->_redirect('*/*/billing');
    }

    /**
     * Multishipping checkout place order page
     *
     * @return void
     */
    public function overviewAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        $this->_getState()->setActiveStep(State::STEP_OVERVIEW);

        try {
            $payment = $this->getRequest()->getPost('payment', array());
            $payment['checks'] = array(
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL
            );
            $this->_getCheckout()->setPaymentMethod($payment);

            $this->_getState()->setCompleteStep(State::STEP_BILLING);

            $this->_view->loadLayout();
            $this->_view->getLayout()->initMessages();
            $this->_view->renderLayout();
        } catch (\Magento\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/billing');
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->messageManager->addException($e, __('We cannot open the overview page.'));
            $this->_redirect('*/*/billing');
        }
    }

    /**
     * Overview action
     *
     * @return void
     */
    public function overviewPostAction()
    {
        if (!$this->_validateMinimumAmount()) {
            return;
        }

        try {
            $requiredAgreements = $this->_objectManager->get(
                'Magento\Checkout\Helper\Data'
            )->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $this->messageManager->addError(
                        __('Please agree to all Terms and Conditions before placing the order.')
                    );
                    $this->_redirect('*/*/billing');
                    return;
                }
            }

            $payment = $this->getRequest()->getPost('payment');
            $paymentInstance = $this->_getCheckout()->getQuote()->getPayment();
            if (isset($payment['cc_number'])) {
                $paymentInstance->setCcNumber($payment['cc_number']);
            }
            if (isset($payment['cc_cid'])) {
                $paymentInstance->setCcCid($payment['cc_cid']);
            }
            $this->_getCheckout()->createOrders();
            $this->_getState()->setActiveStep(State::STEP_SUCCESS);
            $this->_getState()->setCompleteStep(State::STEP_OVERVIEW);
            $this->_getCheckout()->getCheckoutSession()->clearQuote();
            $this->_getCheckout()->getCheckoutSession()->setDisplaySuccess(true);
            $this->_redirect('*/*/success');
        } catch (\Magento\Payment\Model\Info\Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->messageManager->addError($message);
            }
            $this->_redirect('*/*/billing');
        } catch (\Magento\Checkout\Exception $e) {
            $this->_objectManager->get(
                'Magento\Checkout\Helper\Data'
            )->sendPaymentFailedEmail(
                $this->_getCheckout()->getQuote(),
                $e->getMessage(),
                'multi-shipping'
            );
            $this->_getCheckout()->getCheckoutSession()->clearQuote();
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/cart');
        } catch (\Magento\Model\Exception $e) {
            $this->_objectManager->get(
                'Magento\Checkout\Helper\Data'
            )->sendPaymentFailedEmail(
                $this->_getCheckout()->getQuote(),
                $e->getMessage(),
                'multi-shipping'
            );
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('*/*/billing');
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Logger')->logException($e);
            $this->_objectManager->get(
                'Magento\Checkout\Helper\Data'
            )->sendPaymentFailedEmail(
                $this->_getCheckout()->getQuote(),
                $e->getMessage(),
                'multi-shipping'
            );
            $this->messageManager->addError(__('Order place error'));
            $this->_redirect('*/*/billing');
        }
    }

    /**
     * Multishipping checkout success page
     *
     * @return void
     */
    public function successAction()
    {
        if (!$this->_getState()->getCompleteStep(State::STEP_OVERVIEW)) {
            $this->_redirect('*/*/addresses');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $ids = $this->_getCheckout()->getOrderIds();
        $this->_eventManager->dispatch('multishipping_checkout_controller_success_action', array('order_ids' => $ids));
        $this->_view->renderLayout();
    }

    /**
     * Returns before_auth_url redirect parameter for customer session
     *
     * @return string
     */
    public function getCustomerBeforeAuthUrl()
    {
        return $this->_objectManager->create('Magento\UrlInterface')->getUrl('*/*', array('_secure' => true));
    }

    /**
     * Returns a list of action flags [flag_key] => boolean
     *
     * @return array
     */
    public function getActionFlagList()
    {
        return array('redirectLogin' => true);
    }

    /**
     * Returns login url parameter for redirect
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_getHelper()->getMSLoginUrl();
    }

    /**
     * Returns action name which requires redirect
     *
     * @return string
     */
    public function getRedirectActionName()
    {
        return 'index';
    }
}
