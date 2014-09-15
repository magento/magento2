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
namespace Magento\Customer\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Customer\Service\V1\CustomerGroupServiceInterface;

/**
 * Customer account controller
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Account extends \Magento\Framework\App\Action\Action
{
    /**
     * List of actions that are allowed for not authorized users
     *
     * @var string[]
     */
    protected $_openActions = array(
        'create',
        'login',
        'logoutsuccess',
        'forgotpassword',
        'forgotpasswordpost',
        'resetpassword',
        'resetpasswordpost',
        'confirm',
        'confirmation',
        'createpassword',
        'createpost',
        'loginpost'
    );

    /** @var \Magento\Customer\Model\Session */
    protected $_session;

    /** @var \Magento\Customer\Helper\Address */
    protected $_addressHelper;

    /** @var \Magento\Framework\UrlFactory */
    protected $_urlFactory;

    /** @var \Magento\Framework\StoreManagerInterface */
    protected $_storeManager;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $_scopeConfig;

    /** @var CustomerAccountServiceInterface  */
    protected $_customerAccountService;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CustomerAccountServiceInterface $customerAccountService
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerAccountServiceInterface $customerAccountService
    ) {
        $this->_session = $customerSession;
        $this->_addressHelper = $addressHelper;
        $this->_urlFactory = $urlFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerAccountService = $customerAccountService;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session model object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_session;
    }

    /**
     * Get list of actions that are allowed for not authorized users
     *
     * @return string[]
     */
    protected function _getAllowedActions()
    {
        return $this->_openActions;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->getRequest()->isDispatched()) {
            parent::dispatch($request);
        }

        $action = strtolower($this->getRequest()->getActionName());
        $pattern = '/^(' . implode('|', $this->_getAllowedActions()) . ')$/i';

        if (!preg_match($pattern, $action)) {
            if (!$this->_getSession()->authenticate($this)) {
                $this->_actionFlag->set('', 'no-dispatch', true);
            }
        } else {
            $this->_getSession()->setNoReferer(true);
        }
        $this->_view->getPage()->getConfig()->addBodyClass('account');
        $result = parent::dispatch($request);
        $this->_getSession()->unsNoReferer(false);
        return $result;
    }

    /**
     * Adds welcome message and returns success URL
     *
     * @return string
     */
    protected function _welcomeCustomer()
    {
        $this->_addWelcomeMessage();

        $successUrl = $this->_createUrl()->getUrl('*/*/index', array('_secure' => true));
        if (!$this->_scopeConfig->isSetFlag(
            \Magento\Customer\Helper\Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && $this->_getSession()->getBeforeAuthUrl()
        ) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    /**
     * Adds a welcome message to the session
     *
     * @return void
     */
    protected function _addWelcomeMessage()
    {
        $this->messageManager->addSuccess(
            __('Thank you for registering with %1.', $this->_storeManager->getStore()->getFrontendName())
        );
        if ($this->_isVatValidationEnabled()) {
            // Show corresponding VAT message to customer
            $configAddressType = $this->_addressHelper->getTaxCalculationAddressType();
            $editAddersUrl = $this->_createUrl()->getUrl('customer/address/edit');
            switch ($configAddressType) {
                case \Magento\Customer\Helper\Address::TYPE_SHIPPING:
                    // @codingStandardsIgnoreStart
                    $userPrompt = __(
                        'If you are a registered VAT customer, please click <a href="%1">here</a> to enter you shipping address for proper VAT calculation',
                        $editAddersUrl
                    );
                    // @codingStandardsIgnoreEnd
                    break;
                default:
                    // @codingStandardsIgnoreStart
                    $userPrompt = __(
                        'If you are a registered VAT customer, please click <a href="%1">here</a> to enter you billing address for proper VAT calculation',
                        $editAddersUrl
                    );
                    // @codingStandardsIgnoreEnd
                    break;
            }
            $this->messageManager->addSuccess($userPrompt);
        }
    }

    /**
     * Check whether VAT ID validation is enabled
     *
     * @param \Magento\Store\Model\Store|string|int $store
     * @return bool
     */
    protected function _isVatValidationEnabled($store = null)
    {
        return $this->_addressHelper->isVatValidationEnabled($store);
    }

    /**
     * @return \Magento\Framework\UrlInterface
     */
    protected function _createUrl()
    {
        return $this->_urlFactory->create();
    }
}
