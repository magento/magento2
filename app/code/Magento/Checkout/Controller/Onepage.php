<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Action\NotFoundException;
use Magento\Framework\App\RequestInterface;

class Onepage extends Action
{
    /**
     * @var array
     */
    protected $_sectionUpdateFunctions = [
        'payment-method' => '_getPaymentMethodsHtml',
        'shipping-method' => '_getShippingMethodsHtml',
        'review' => '_getReviewHtml',
    ];

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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Sales\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Sales\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Sales\Model\QuoteRepository $quoteRepository
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_translateInline = $translateInline;
        $this->_formKeyValidator = $formKeyValidator;
        $this->scopeConfig = $scopeConfig;
        $this->layoutFactory = $layoutFactory;
        $this->quoteRepository = $quoteRepository;
        parent::__construct($context, $customerSession, $customerRepository, $accountManagement);
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
            ['index', 'progress']
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
        $layout = $this->layoutFactory->create();
        $layout->getUpdate()->load([$handle]);
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
     * Get progress html checkout step
     *
     * @param string $checkoutStep
     * @return mixed
     */
    protected function getProgressHtml($checkoutStep = '')
    {
        $layout = $this->layoutFactory->create();
        $layout->getUpdate()->load(['checkout_onepage_progress']);
        $layout->generateXml();
        $layout->generateElements();

        $block = $layout->getBlock('progress')->setAttribute('next_step', $checkoutStep);
        return $block->toHtml();
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
