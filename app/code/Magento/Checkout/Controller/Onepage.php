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
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CustomerAccountService $customerAccountService
     * @param CustomerMetadataService $customerMetadataService
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerAccountService $customerAccountService,
        CustomerMetadataService $customerMetadataService,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_translateInline = $translateInline;
        $this->_formKeyValidator = $formKeyValidator;
        $this->scopeConfig = $scopeConfig;
        $this->layoutFactory = $layoutFactory;
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
