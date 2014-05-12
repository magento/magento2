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
namespace Magento\Persistent\Model;

/**
 * Persistent Observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Observer
{
    /**
     * Whether set quote to be persistent in workflow
     *
     * @var bool
     */
    protected $_setQuotePersistent = true;

    /**
     * Persistent data
     *
     * @var \Magento\Persistent\Helper\Data
     */
    protected $_persistentData = null;

    /**
     * Persistent session
     *
     * @var \Magento\Persistent\Helper\Session
     */
    protected $_persistentSession = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * Layout model
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Request http
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestHttp;

    /**
     * Persistent config factory
     *
     * @var \Magento\Persistent\Model\Persistent\ConfigFactory
     */
    protected $_persistentConfigFactory;

    /**
     * Quote factory
     *
     * @var \Magento\Sales\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * Session factory
     *
     * @var \Magento\Persistent\Model\SessionFactory
     */
    protected $_sessionFactory;

    /**
     * Url model
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Website collection factory
     *
     * @var \Magento\Store\Model\Resource\Website\CollectionFactory
     */
    protected $_websiteCollectionFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Checkout\Helper\ExpressRedirect
     */
    protected $_expressRedirectHelper;

    /**
     * Customer view helper
     *
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * Customer account service
     *
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $_customerAccountService;

    /**
     * Construct
     *
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Persistent\Helper\Session $persistentSession
     * @param \Magento\Persistent\Helper\Data $persistentData
     * @param \Magento\Store\Model\Resource\Website\CollectionFactory $websiteCollectionFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Persistent\Model\SessionFactory $sessionFactory
     * @param \Magento\Sales\Model\QuoteFactory $quoteFactory
     * @param \Magento\Persistent\Model\Persistent\ConfigFactory $persistentConfigFactory
     * @param \Magento\Framework\App\RequestInterface $requestHttp
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Helper\ExpressRedirect $expressRedirectHelper
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Persistent\Helper\Session $persistentSession,
        \Magento\Persistent\Helper\Data $persistentData,
        \Magento\Store\Model\Resource\Website\CollectionFactory $websiteCollectionFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $url,
        \Magento\Persistent\Model\SessionFactory $sessionFactory,
        \Magento\Sales\Model\QuoteFactory $quoteFactory,
        \Magento\Persistent\Model\Persistent\ConfigFactory $persistentConfigFactory,
        \Magento\Framework\App\RequestInterface $requestHttp,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Helper\ExpressRedirect $expressRedirectHelper,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService
    ) {
        $this->_eventManager = $eventManager;
        $this->_persistentSession = $persistentSession;
        $this->_persistentData = $persistentData;
        $this->_websiteCollectionFactory = $websiteCollectionFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_url = $url;
        $this->_sessionFactory = $sessionFactory;
        $this->_quoteFactory = $quoteFactory;
        $this->_persistentConfigFactory = $persistentConfigFactory;
        $this->_requestHttp = $requestHttp;
        $this->_layout = $layout;
        $this->_escaper = $escaper;
        $this->messageManager = $messageManager;
        $this->_expressRedirectHelper = $expressRedirectHelper;
        $this->_customerViewHelper = $customerViewHelper;
        $this->_customerAccountService = $customerAccountService;
    }

    /**
     * Apply persistent data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function applyPersistentData($observer)
    {
        if (!$this->_persistentData->canProcess(
            $observer
        ) || !$this->_persistentSession->isPersistent() || $this->_customerSession->isLoggedIn()
        ) {
            return $this;
        }
        /** @var \Magento\Persistent\Model\Persistent\Config $persistentConfig */
        $persistentConfig = $this->_persistentConfigFactory->create();
        $persistentConfig->setConfigFilePath($this->_persistentData->getPersistentConfigFilePath())->fire();
        return $this;
    }

    /**
     * Apply persistent data to specific block
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function applyBlockPersistentData($observer)
    {
        if (!$this->_persistentSession->isPersistent() || $this->_customerSession->isLoggedIn()) {
            return $this;
        }

        /** @var $block \Magento\Framework\View\Element\AbstractBlock */
        $block = $observer->getEvent()->getBlock();

        if (!$block) {
            return $this;
        }

        $configFilePath = $observer->getEvent()->getConfigFilePath();
        if (!$configFilePath) {
            $configFilePath = $this->_persistentData->getPersistentConfigFilePath();
        }

        /** @var $persistentConfig \Magento\Persistent\Model\Persistent\Config */
        $persistentConfig = $this->_persistentConfigFactory->create();
        $persistentConfig->setConfigFilePath($configFilePath);

        foreach ($persistentConfig->getBlockConfigInfo(get_class($block)) as $persistentConfigInfo) {
            $persistentConfig->fireOne($persistentConfigInfo, $block);
        }

        return $this;
    }

    /**
     * Emulate 'welcome' block with persistent data
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return $this
     */
    public function emulateWelcomeBlock($block)
    {
        $escapedName = $this->_escaper->escapeHtml(
            $this->_customerViewHelper->getCustomerName($this->_getPersistentCustomer()),
            null
        );

        $this->_applyAccountLinksPersistentData();
        $welcomeMessage = __('Welcome, %1!', $escapedName)
            . ' ' . $this->_layout->getBlock('header.additional')->toHtml();
        $block->setWelcome($welcomeMessage);
        return $this;
    }

    /**
     * Emulate 'account links' block with persistent data
     *
     * @return void
     */
    protected function _applyAccountLinksPersistentData()
    {
        if (!$this->_layout->getBlock('header.additional')) {
            $this->_layout->addBlock('Magento\Persistent\Block\Header\Additional', 'header.additional');
        }
    }

    /**
     * Emulate 'top links' block with persistent data
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return void
     */
    public function emulateTopLinks($block)
    {
        $this->_applyAccountLinksPersistentData();
        $block->removeLinkByUrl($this->_url->getUrl('customer/account/login'));
    }

    /**
     * Emulate quote by persistent data
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function emulateQuote($observer)
    {
        $stopActions = array('persistent_index_saveMethod', 'customer_account_createpost');

        if (!$this->_persistentData->canProcess($observer)
            || !$this->_persistentSession->isPersistent()
            || $this->_customerSession->isLoggedIn()
        ) {
            return;
        }

        $actionName = $observer->getEvent()->getRequest()->getFullActionName();

        if (in_array($actionName, $stopActions)) {
            return;
        }

        if ($this->_isShoppingCartPersist()) {
            $this->_checkoutSession->setCustomerData($this->_getPersistentCustomer());
            if (!$this->_checkoutSession->hasQuote()) {
                $this->_checkoutSession->getQuote();
            }
        }
    }

    /**
     * Set persistent data into quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function setQuotePersistentData($observer)
    {
        if (!$this->_isPersistent()) {
            return;
        }

        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $observer->getEvent()->getQuote();
        if (!$quote) {
            return;
        }

        if ($this->_isGuestShoppingCart() && $this->_setQuotePersistent) {
            //Quote is not actual customer's quote, just persistent
            $quote->setIsActive(false)->setIsPersistent(true);
        }
    }

    /**
     * Set quote to be loaded even if not active
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function setLoadPersistentQuote($observer)
    {
        if (!$this->_isGuestShoppingCart()) {
            return;
        }

        if ($this->_checkoutSession) {
            $this->_checkoutSession->setLoadInactive();
        }
    }

    /**
     * Prevent clear checkout session
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function preventClearCheckoutSession($observer)
    {
        $action = $this->_checkClearCheckoutSessionNecessity($observer);

        if ($action) {
            $action->setClearCheckoutSession(false);
        }
    }

    /**
     * Make persistent quote to be guest
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function makePersistentQuoteGuest($observer)
    {
        if (!$this->_checkClearCheckoutSessionNecessity($observer)) {
            return;
        }

        $this->setQuoteGuest(true);
    }

    /**
     * Check if checkout session should NOT be cleared
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|\Magento\Persistent\Controller\Index
     */
    protected function _checkClearCheckoutSessionNecessity($observer)
    {
        if (!$this->_isGuestShoppingCart()) {
            return false;
        }

        /** @var $action \Magento\Persistent\Controller\Index */
        $action = $observer->getEvent()->getControllerAction();
        if ($action instanceof \Magento\Persistent\Controller\Index) {
            return $action;
        }

        return false;
    }

    /**
     * Reset session data when customer re-authenticates
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function customerAuthenticatedEvent($observer)
    {
        $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);

        if ($this->_requestHttp->getParam('context') != 'checkout') {
            $this->_expirePersistentSession();
            return;
        }

        $this->setQuoteGuest();
    }

    /**
     * Unset persistent cookie and make customer's quote as a guest
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function removePersistentCookie($observer)
    {
        if (!$this->_persistentData->canProcess($observer) || !$this->_isPersistent()) {
            return;
        }

        $this->_persistentSession->getSession()->removePersistentCookie();

        if (!$this->_customerSession->isLoggedIn()) {
            $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);
        }

        $this->setQuoteGuest();
    }

    /**
     * Disable guest checkout if we are in persistent mode
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function disableGuestCheckout($observer)
    {
        if ($this->_persistentSession->isPersistent()) {
            $observer->getEvent()->getResult()->setIsAllowed(false);
        }
    }

    /**
     * Prevent express checkout
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function preventExpressCheckout(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_isLoggedOut()) {
            return;
        }

        /** @var $controllerAction \Magento\Checkout\Controller\Express\RedirectLoginInterface*/
        $controllerAction = $observer->getEvent()->getControllerAction();
        if (!$controllerAction ||
            !$controllerAction instanceof \Magento\Checkout\Controller\Express\RedirectLoginInterface ||
            $controllerAction->getRedirectActionName() != $controllerAction->getRequest()->getActionName()
        ) {
            return;
        }

        $this->messageManager->addNotice(__('To check out, please log in using your email address.'));
        $customerBeforeAuthUrl = $this->_url->getUrl('persistent/index/expressCheckout');

        $this->_expressRedirectHelper->redirectLogin($controllerAction, $customerBeforeAuthUrl);
    }

    /**
     * Retrieve persistent customer instance
     *
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    protected function _getPersistentCustomer()
    {
        return $this->_customerAccountService->getCustomer($this->_persistentSession->getSession()->getCustomerId());
    }

    /**
     * Return current active quote for persistent customer
     *
     * @return \Magento\Sales\Model\Quote
     */
    protected function _getQuote()
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $this->_quoteFactory->create();
        $quote->loadByCustomer($this->_persistentSession->getSession()->getCustomerId());
        return $quote;
    }

    /**
     * Check whether shopping cart is persistent
     *
     * @return bool
     */
    protected function _isShoppingCartPersist()
    {
        return $this->_persistentData->isShoppingCartPersist();
    }

    /**
     * Check whether persistent mode is running
     *
     * @return bool
     */
    protected function _isPersistent()
    {
        return $this->_persistentSession->isPersistent();
    }

    /**
     * Check if persistent mode is running and customer is logged out
     *
     * @return bool
     */
    protected function _isLoggedOut()
    {
        return $this->_isPersistent() && !$this->_customerSession->isLoggedIn();
    }

    /**
     * Check if shopping cart is guest while persistent session and user is logged out
     *
     * @return bool
     */
    protected function _isGuestShoppingCart()
    {
        return $this->_isLoggedOut() && !$this->_persistentData->isShoppingCartPersist();
    }

    /**
     * Make quote to be guest
     *
     * @param bool $checkQuote Check quote to be persistent (not stolen)
     * @return void
     */
    public function setQuoteGuest($checkQuote = false)
    {
        /** @var $quote \Magento\Sales\Model\Quote */
        $quote = $this->_checkoutSession->getQuote();
        if ($quote && $quote->getId()) {
            if ($checkQuote && !$this->_persistentData->isShoppingCartPersist() && !$quote->getIsPersistent()) {
                $this->_checkoutSession->clearQuote()->clearStorage();
                return;
            }

            $quote->getPaymentsCollection()->walk('delete');
            $quote->getAddressesCollection()->walk('delete');
            $this->_setQuotePersistent = false;
            $quote->setIsActive(
                true
            )->setCustomerId(
                null
            )->setCustomerEmail(
                null
            )->setCustomerFirstname(
                null
            )->setCustomerLastname(
                null
            )->setCustomerGroupId(
                \Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID
            )->setIsPersistent(
                false
            )->removeAllAddresses();
            //Create guest addresses
            $quote->getShippingAddress();
            $quote->getBillingAddress();
            $quote->collectTotals()->save();
        }

        $this->_persistentSession->getSession()->removePersistentCookie();
    }

    /**
     * Check and clear session data if persistent session expired
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function checkExpirePersistentQuote(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_persistentData->canProcess($observer)) {
            return;
        }

        if ($this->_persistentData->isEnabled() &&
            !$this->_isPersistent() &&
            !$this->_customerSession->isLoggedIn() &&
            $this->_checkoutSession->getQuoteId() &&
            !$observer->getControllerAction() instanceof \Magento\Checkout\Controller\Onepage
            // persistent session does not expire on onepage checkout page to not spoil customer group id
        ) {
            $this->_eventManager->dispatch('persistent_session_expired');
            $this->_expirePersistentSession();
            $this->_customerSession->setCustomerId(null)->setCustomerGroupId(null);
        }
    }

    /**
     * @return void
     */
    protected function _expirePersistentSession()
    {
        $quote = $this->_checkoutSession->setLoadInactive()->getQuote();
        if ($quote->getIsActive() && $quote->getCustomerId()) {
            $this->_checkoutSession->setCustomerData(null)->clearQuote()->clearStorage();
        } else {
            $quote
                ->setIsActive(true)
                ->setIsPersistent(false)
                ->setCustomerId(null)
                ->setCustomerGroupId(\Magento\Customer\Service\V1\CustomerGroupServiceInterface::NOT_LOGGED_IN_ID);
        }
    }

    /**
     * Clear expired persistent sessions
     *
     * @param \Magento\Cron\Model\Schedule $schedule
     * @return $this
     */
    public function clearExpiredCronJob(\Magento\Cron\Model\Schedule $schedule)
    {
        $websiteIds = $this->_websiteCollectionFactory->create()->getAllIds();
        if (!is_array($websiteIds)) {
            return $this;
        }

        foreach ($websiteIds as $websiteId) {
            $this->_sessionFactory->create()->deleteExpired($websiteId);
        }

        return $this;
    }

    /**
     * Update customer id and customer group id if user is in persistent session
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function updateCustomerCookies(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_isPersistent()) {
            return;
        }

        $customerCookies = $observer->getEvent()->getCustomerCookies();
        if ($customerCookies instanceof \Magento\Framework\Object) {
            $persistentCustomer = $this->_getPersistentCustomer();
            $customerCookies->setCustomerId($persistentCustomer->getId());
            $customerCookies->setCustomerGroupId($persistentCustomer->getGroupId());
        }
    }

    /**
     * Set persistent data to customer session
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function emulateCustomer($observer)
    {
        if (!$this->_persistentData->canProcess($observer) || !$this->_isShoppingCartPersist()) {
            return $this;
        }

        if ($this->_isLoggedOut()) {
            $customer = $this->_getPersistentCustomer();
            $this->_customerSession->setCustomerId($customer->getId())->setCustomerGroupId($customer->getGroupId());
        }
        return $this;
    }
}
