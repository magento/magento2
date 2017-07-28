<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
abstract class Onepage extends Action
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $_sectionUpdateFunctions = [
        'payment-method' => '_getPaymentMethodsHtml',
        'shipping-method' => '_getShippingMethodsHtml',
        'review' => '_getReviewHtml',
    ];

    /**
     * @var \Magento\Sales\Model\Order
     * @since 2.0.0
     */
    protected $_order;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Translate\InlineInterface
     * @since 2.0.0
     */
    protected $_translateInline;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     * @since 2.0.0
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     * @since 2.0.0
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     * @since 2.0.0
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     * @since 2.0.0
     */
    protected $resultLayoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     * @since 2.0.0
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $accountManagement
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     *
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_translateInline = $translateInline;
        $this->_formKeyValidator = $formKeyValidator;
        $this->scopeConfig = $scopeConfig;
        $this->layoutFactory = $layoutFactory;
        $this->quoteRepository = $quoteRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     * @since 2.0.0
     */
    public function dispatch(RequestInterface $request)
    {
        $this->_request = $request;
        $result = $this->_preDispatchValidateCustomer();
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getQuote();
        if ($quote->isMultipleShippingAddresses()) {
            $quote->removeAllAddresses();
        }

        if (!$this->_canShowForUnregisteredUsers()) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     * @since 2.0.0
     */
    protected function _ajaxRedirectResponse()
    {
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setStatusHeader(403, '1.1', 'Session Expired')
            ->setHeader('Login-Required', 'true');
        return $resultRaw;
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _expireAjax()
    {
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if ($this->_objectManager->get(\Magento\Checkout\Model\Session::class)->getCartWasUpdated(true)
            &&
            !in_array($action, ['index', 'progress'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * Render HTML based on requested layout handle name
     *
     * @param string $handle
     * @return string
     * @since 2.0.0
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
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    protected function _getShippingMethodsHtml()
    {
        return $this->_getHtmlByHandle('checkout_onepage_shippingmethod');
    }

    /**
     * Get payment method step html
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getOnepage()
    {
        return $this->_objectManager->get(\Magento\Checkout\Model\Type\Onepage::class);
    }

    /**
     * Check can page show for unregistered users
     *
     * @return boolean
     * @since 2.0.0
     */
    protected function _canShowForUnregisteredUsers()
    {
        return $this->_objectManager->get(
            \Magento\Customer\Model\Session::class
        )->isLoggedIn() || $this->getRequest()->getActionName() == 'index' || $this->_objectManager->get(
            \Magento\Checkout\Helper\Data::class
        )->isAllowedGuestCheckout(
            $this->getOnepage()->getQuote()
        ) || !$this->_objectManager->get(
            \Magento\Checkout\Helper\Data::class
        )->isCustomerMustBeLogged();
    }
}
