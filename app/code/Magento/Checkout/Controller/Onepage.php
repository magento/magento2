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
 * @category    Magento
 * @package     Magento_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Checkout\Controller;

class Onepage extends \Magento\Checkout\Controller\Action
{
    /**
     * @var array
     */
    protected $_sectionUpdateFunctions = array(
        'payment-method'  => '_getPaymentMethodsHtml',
        'shipping-method' => '_getShippingMethodsHtml',
        'review'          => '_getReviewHtml',
    );

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Model\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Model\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $customerSession);
    }

    /**
     * @return \Magento\Checkout\Controller\Onepage|null
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();

        $checkoutSessionQuote = $this->_objectManager->get('Magento\Checkout\Model\Session')->getQuote();
        if ($checkoutSessionQuote->getIsMultiShipping()) {
            $checkoutSessionQuote->setIsMultiShipping(false);
            $checkoutSessionQuote->removeAllAddresses();
        }

        if (!$this->_canShowForUnregisteredUsers()) {
            $this->norouteAction();
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return null;
        }

        return $this;
    }

    /**
     * @return \Magento\Checkout\Controller\Onepage
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        if (!$this->getOnepage()->getQuote()->hasItems()
            || $this->getOnepage()->getQuote()->getHasError()
            || $this->getOnepage()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    /**
     * Render HTML based on requested layout handle name
     *
     * @param string $handle
     * @return string
     */
    protected function _getHtmlByHandle($handle)
    {
        $layout = $this->getLayout();
        $layout->getUpdate()->addPageHandles(array($handle));
        $layout->getUpdate()->load();
        $layout->generateXml();
        $layout->generateElements();
        $output = $layout->getOutput();
        $this->_objectManager->get('Magento\Core\Model\Translate')->processResponseBody($output);
        return $output;
    }


    /**
     * Get shipping method step html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        return $this->_getHtmlByHandle('checkout_onepage_shippingmethod');
    }

    /**
     * Get payment method step html
     *
     * @return string
     */
    protected function _getPaymentMethodsHtml()
    {
        return $this->_getHtmlByHandle('checkout_onepage_paymentmethod');
    }

    /**
     * @return string
     */
    protected function _getAdditionalHtml()
    {
        return $this->_getHtmlByHandle('checkout_onepage_additional');
    }

    /**
     * Get order review step html
     *
     * @return string
     */
    protected function _getReviewHtml()
    {
        return $this->_getHtmlByHandle('checkout_onepage_review');
    }

    /**
     * Get one page checkout model
     *
     * @return \Magento\Checkout\Model\Type\Onepage
     */
    public function getOnepage()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Type\Onepage');
    }

    /**
     * Checkout page
     */
    public function indexAction()
    {
        if (!$this->_objectManager->get('Magento\Checkout\Helper\Data')->canOnepageCheckout()) {
            $this->_objectManager->get('Magento\Checkout\Model\Session')->addError(__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = $this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfig('sales/minimum_order/error_message') ?
                $this->_objectManager->get('Magento\Core\Model\Store\Config')->getConfig('sales/minimum_order/error_message') :
                __('Subtotal must exceed minimum order amount');

            $this->_objectManager->get('Magento\Checkout\Model\Session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $currentUrl = $this->_objectManager->create('Magento\UrlInterface')
            ->getUrl('*/*/*', array('_secure'=>true));
        $this->_objectManager->get('Magento\Customer\Model\Session')->setBeforeAuthUrl($currentUrl);
        $this->getOnepage()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('Magento\Customer\Model\Session');
        $this->getLayout()->getBlock('head')->setTitle(__('Checkout'));
        $this->renderLayout();
    }

    /**
     * Checkout status block
     */
    public function progressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $this->addPageLayoutHandles();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function shippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $this->addPageLayoutHandles();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    public function reviewAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $this->addPageLayoutHandles();
        $this->loadLayout(false);
        $this->renderLayout();
    }

    /**
     * Order success action
     */
    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$session->getLastSuccessQuoteId()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $session->getLastQuoteId();
        $lastOrderId = $session->getLastOrderId();
        $lastRecurringProfiles = $session->getLastRecurringProfileIds();
        if (!$lastQuoteId || (!$lastOrderId && empty($lastRecurringProfiles))) {
            $this->_redirect('checkout/cart');
            return;
        }

        $session->clear();
        $this->loadLayout();
        $this->_initLayoutMessages('Magento\Checkout\Model\Session');
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action', array('order_ids' => array($lastOrderId))
        );
        $this->renderLayout();
    }

    public function failureAction()
    {
        $lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->loadLayout();
        $this->renderLayout();
    }


    public function getAdditionalAction()
    {
        $this->getResponse()->setBody($this->_getAdditionalHtml());
    }

    /**
     * Address JSON
     */
    public function getAddressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $addressId = $this->getRequest()->getParam('address', false);
        if ($addressId) {
            $address = $this->getOnepage()->getAddress($addressId);

            $customerSession = $this->_objectManager->get('Magento\Customer\Model\Session');
            if ($customerSession->getCustomer()->getId() == $address->getCustomerId()) {
                $this->getResponse()->setHeader('Content-type', 'application/x-json');
                $this->getResponse()->setBody($address->toJson());
            } else {
                $this->getResponse()->setHeader('HTTP/1.1', '403 Forbidden');
            }
        }
    }

    /**
     * Save checkout method
     */
    public function saveMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $method = $this->getRequest()->getPost('method');
            $result = $this->getOnepage()->saveCheckoutMethod($method);
            $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
        }
    }

    /**
     * save checkout billing address
     */
    public function saveBillingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                if ($this->getOnepage()->getQuote()->isVirtual()) {
                    $result['goto_section'] = 'payment';
                    $result['update_section'] = array(
                        'name' => 'payment-method',
                        'html' => $this->_getPaymentMethodsHtml()
                    );
                } elseif (isset($data['use_for_shipping']) && $data['use_for_shipping'] == 1) {
                    $result['goto_section'] = 'shipping_method';
                    $result['update_section'] = array(
                        'name' => 'shipping-method',
                        'html' => $this->_getShippingMethodsHtml()
                    );

                    $result['allow_sections'] = array('shipping');
                    $result['duplicateBillingInfo'] = 'true';
                } else {
                    $result['goto_section'] = 'shipping';
                }
            }

            $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
        }
    }

    /**
     * Shipping address save action
     */
    public function saveShippingAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping', array());
            $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
            $result = $this->getOnepage()->saveShipping($data, $customerAddressId);

            if (!isset($result['error'])) {
                $result['goto_section'] = 'shipping_method';
                $result['update_section'] = array(
                    'name' => 'shipping-method',
                    'html' => $this->_getShippingMethodsHtml()
                );
            }
            $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
        }
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
            // $result will contain error data if shipping method is empty
            if(!$result) {
                $this->_eventManager->dispatch('checkout_controller_onepage_save_shipping_method',
                        array('request'=>$this->getRequest(),
                            'quote'=>$this->getOnepage()->getQuote()));
                $this->getOnepage()->getQuote()->collectTotals();
                $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
        }
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     */
    public function savePaymentAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }

            $data = $this->getRequest()->getPost('payment', array());
            $result = $this->getOnepage()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $result['goto_section'] = 'review';
                $result['update_section'] = array(
                    'name' => 'review',
                    'html' => $this->_getReviewHtml()
                );
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (\Magento\Payment\Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (\Magento\Core\Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $result['error'] = __('Unable to set Payment Method');
        }
        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
    }

    /**
     * Get Order by quoteId
     *
     * @return \Magento\Sales\Model\Order
     * @throws \Magento\Payment\Model\Info\Exception
     */
    protected function _getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = $this->_objectManager->create('Magento\Sales\Model\Order');
            $this->_order->load($this->getOnepage()->getQuote()->getId(), 'quote_id');
            if (!$this->_order->getId()) {
                throw new \Magento\Payment\Model\Info\Exception(
                    __('Can not create invoice. Order was not found.')
                );
            }
        }
        return $this->_order;
    }

    /**
     * Create invoice
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    protected function _initInvoice()
    {
        $items = array();
        foreach ($this->_getOrder()->getAllItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }
        /* @var $invoice \Magento\Sales\Model\Service\Order */
        $invoice = $this->_objectManager
            ->create('Magento\Sales\Model\Service\Order', array('order' => $this->_getOrder()))
            ->prepareInvoice($items);
        $invoice->setEmailSent(true)->register();

        $this->_coreRegistry->register('current_invoice', $invoice);
        return $invoice;
    }

    /**
     * Create order action
     */
    public function saveOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            $requiredAgreements = $this->_objectManager->get('Magento\Checkout\Helper\Data')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $agreementsDiff = array_diff($requiredAgreements, $postedAgreements);
                if ($agreementsDiff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = __('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
                    return;
                }
            }

            $data = $this->getRequest()->getPost('payment', array());
            if ($data) {
                $data['checks'] = \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT
                    | \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY
                    | \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY
                    | \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX
                    | \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (\Magento\Payment\Model\Info\Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array(
                'name' => 'payment-method',
                'html' => $this->_getPaymentMethodsHtml()
            );
        } catch (\Magento\Core\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_objectManager->get('Magento\Checkout\Helper\Data')->sendPaymentFailedEmail(
                $this->getOnepage()->getQuote(),
                $e->getMessage()
            );
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage();
            $gotoSection = $this->getOnepage()->getCheckout()->getGotoSection();
            if ($gotoSection) {
                $result['goto_section'] = $gotoSection;
                $this->getOnepage()->getCheckout()->setGotoSection(null);
            }

            $updateSection = $this->getOnepage()->getCheckout()->getUpdateSection();
            if ($updateSection) {
                if (isset($this->_sectionUpdateFunctions[$updateSection])) {
                    $updateSectionFunction = $this->_sectionUpdateFunctions[$updateSection];
                    $result['update_section'] = array(
                        'name' => $updateSection,
                        'html' => $this->$updateSectionFunction()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Core\Model\Logger')->logException($e);
            $this->_objectManager->get('Magento\Checkout\Helper\Data')->sendPaymentFailedEmail(
                $this->getOnepage()->getQuote(),
                $e->getMessage()
            );
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = __('Something went wrong processing your order. Please try again later.');
        }
        $this->getOnepage()->getQuote()->save();
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl)) {
            $result['redirect'] = $redirectUrl;
        }

        $this->getResponse()->setBody($this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result));
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array
     * @return array
     */
    protected function _filterPostData($data)
    {
        $data = $this->_filterDates($data, array('dob'));
        return $data;
    }

    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn()
            || $this->getRequest()->getActionName() == 'index'
            || $this->_objectManager->get('Magento\Checkout\Helper\Data')->isAllowedGuestCheckout($this->getOnepage()->getQuote())
            || !$this->_objectManager->get('Magento\Checkout\Helper\Data')->isCustomerMustBeLogged();
    }
}
