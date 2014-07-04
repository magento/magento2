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
namespace Magento\Checkout\Controller;

use Magento\Framework\App\Action\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface as CustomerAccountService;
use Magento\Customer\Service\V1\CustomerMetadataServiceInterface as CustomerMetadataService;

class Onepage extends Action
{
    /**
     * @var array
     */
    protected $_sectionUpdateFunctions = array(
        'payment-method' => '_getPaymentMethodsHtml',
        'shipping-method' => '_getShippingMethodsHtml',
        'review' => '_getReviewHtml'
    );

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     */
    protected $_translateInline;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerAccountService $customerAccountService
     * @param CustomerMetadataService $customerMetadataService
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerAccountService $customerAccountService,
        CustomerMetadataService $customerMetadataService,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_translateInline = $translateInline;
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context, $customerSession, $customerAccountService, $customerMetadataService);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\App\Action\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_request = $request;
        $this->_preDispatchValidateCustomer();

        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->_objectManager->get('Magento\Checkout\Model\Session')->getQuote();
        if ($quote->isMultipleShippingAddresses()) {
            $quote->removeAllAddresses();
        }

        if (!$this->_canShowForUnregisteredUsers()) {
            throw new NotFoundException();
        }
        return parent::dispatch($request);
    }

    /**
     * @return $this
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired')->setHeader('Login-Required', 'true');
        return $this;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if ($this->_objectManager->get(
            'Magento\Checkout\Model\Session'
        )->getCartWasUpdated(
            true
        ) && !in_array(
            $action,
            array('index', 'progress')
        )
        ) {
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
        $layout = $this->_view->getLayout();
        $layout->getUpdate()->addPageHandles(array($handle));
        $layout->getUpdate()->load();
        $layout->generateXml();
        $layout->generateElements();
        $output = $layout->getOutput();
        $this->_translateInline->processResponseBody($output);
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
     *
     * @return void
     */
    public function indexAction()
    {
        if (!$this->_objectManager->get('Magento\Checkout\Helper\Data')->canOnepageCheckout()) {
            $this->messageManager->addError(__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_objectManager->get('Magento\Checkout\Model\Session')->setCartWasUpdated(false);
        $currentUrl = $this->_objectManager->create('Magento\Framework\UrlInterface')->getUrl('*/*/*', array('_secure' => true));
        $this->_objectManager->get('Magento\Customer\Model\Session')->setBeforeAuthUrl($currentUrl);
        $this->getOnepage()->initCheckout();
        $this->_view->loadLayout();
        $layout = $this->_view->getLayout();
        $layout->initMessages();
        $layout->getBlock('head')->setTitle(__('Checkout'));
        $this->_view->renderLayout();
    }

    /**
     * Checkout status block
     *
     * @return void
     */
    public function progressAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $this->_view->addPageLayoutHandles();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function shippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $this->_view->addPageLayoutHandles();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function reviewAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $this->_view->addPageLayoutHandles();
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }

    /**
     * Order success action
     *
     * @return void
     */
    public function successAction()
    {
        $session = $this->getOnepage()->getCheckout();
        if (!$this->_objectManager->get('Magento\Checkout\Model\Session\SuccessValidator')->isValid($session)) {
            $this->_redirect('checkout/cart');
            return;
        }
        $session->clearQuote();
        //@todo: Refactor it to match CQRS
        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_eventManager->dispatch(
            'checkout_onepage_controller_success_action',
            array('order_ids' => array($session->getLastOrderId()))
        );
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function failureAction()
    {
        $lastQuoteId = $this->getOnepage()->getCheckout()->getLastQuoteId();
        $lastOrderId = $this->getOnepage()->getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * @return void
     */
    public function getAdditionalAction()
    {
        $this->getResponse()->setBody($this->_getAdditionalHtml());
    }

    /**
     * Save checkout method
     *
     * @return void
     */
    public function saveMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $method = $this->getRequest()->getPost('method');
            $result = $this->getOnepage()->saveCheckoutMethod($method);
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Save checkout billing address
     *
     * @return void
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

            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Shipping address save action
     *
     * @return void
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
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Shipping method save action
     *
     * @return void
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
            if (!$result) {
                $this->_eventManager->dispatch(
                    'checkout_controller_onepage_save_shipping_method',
                    array('request' => $this->getRequest(), 'quote' => $this->getOnepage()->getQuote())
                );
                $this->getOnepage()->getQuote()->collectTotals();
                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
                );

                $result['goto_section'] = 'payment';
                $result['update_section'] = array(
                    'name' => 'payment-method',
                    'html' => $this->_getPaymentMethodsHtml()
                );
            }
            $this->getOnepage()->getQuote()->collectTotals()->save();
            $this->getResponse()->representJson(
                $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
            );
        }
    }

    /**
     * Save payment ajax action
     *
     * Sets either redirect or a JSON response
     *
     * @return void
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
                $result['update_section'] = array('name' => 'review', 'html' => $this->_getReviewHtml());
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (\Magento\Payment\Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (\Magento\Framework\Model\Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $result['error'] = __('Unable to set Payment Method');
        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
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
                throw new \Magento\Payment\Model\Info\Exception(__('Can not create invoice. Order was not found.'));
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
        $invoice = $this->_objectManager->create(
            'Magento\Sales\Model\Service\Order',
            array('order' => $this->_getOrder())
        )->prepareInvoice(
            $items
        );
        $invoice->setEmailSent(true)->register();

        $this->_coreRegistry->register('current_invoice', $invoice);
        return $invoice;
    }

    /**
     * Create order action
     *
     * @return void
     */
    public function saveOrderAction()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->_expireAjax()) {
            return;
        }

        $result = array();
        try {
            $agreementsValidator = $this->_objectManager->get('Magento\Checkout\Model\Agreements\AgreementsValidator');
            if (!$agreementsValidator->isValid(array_keys($this->getRequest()->getPost('agreement', array())))) {
                $result['success'] = false;
                $result['error'] = true;
                $result['error_messages'] = __(
                    'Please agree to all the terms and conditions before placing the order.'
                );
                $this->getResponse()->representJson(
                    $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
                );
                return;
            }

            $data = $this->getRequest()->getPost('payment', array());
            if ($data) {
                $data['checks'] = array(
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_CHECKOUT,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
                    \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL
                );
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
            }

            $this->getOnepage()->saveOrder();

            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error'] = false;
        } catch (\Magento\Payment\Model\Info\Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $result['error_messages'] = $message;
            }
            $result['goto_section'] = 'payment';
            $result['update_section'] = array('name' => 'payment-method', 'html' => $this->_getPaymentMethodsHtml());
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->_objectManager->get(
                'Magento\Checkout\Helper\Data'
            )->sendPaymentFailedEmail(
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
                        'html' => $this->{$updateSectionFunction}()
                    );
                }
                $this->getOnepage()->getCheckout()->setUpdateSection(null);
            }
        } catch (\Exception $e) {
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->_objectManager->get(
                'Magento\Checkout\Helper\Data'
            )->sendPaymentFailedEmail(
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

        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Core\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
        return $this->_objectManager->get(
            'Magento\Customer\Model\Session'
        )->isLoggedIn() || $this->getRequest()->getActionName() == 'index' || $this->_objectManager->get(
            'Magento\Checkout\Helper\Data'
        )->isAllowedGuestCheckout(
            $this->getOnepage()->getQuote()
        ) || !$this->_objectManager->get(
            'Magento\Checkout\Helper\Data'
        )->isCustomerMustBeLogged();
    }
}
