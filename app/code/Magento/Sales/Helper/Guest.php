<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Helper;

use Magento\Framework\App as App;

/**
 * Sales module base helper
 */
class Guest extends \Magento\Core\Helper\Data
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Cookie key for guest view
     */
    const COOKIE_NAME = 'guest-view';

    /**
     * Cookie path
     */
    const COOKIE_PATH = '/';

    /**
     * Cookie lifetime value
     */
    const COOKIE_LIFETIME = 600;

    /**
     * @param App\Helper\Context $context
     * @param App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\ViewInterface $view
     * @param bool $dbCompatibleMode
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        App\Helper\Context $context,
        App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\ViewInterface $view,
        $dbCompatibleMode = true
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->messageManager = $messageManager;
        $this->orderFactory = $orderFactory;
        $this->_view = $view;
        parent::__construct(
            $context,
            $scopeConfig,
            $storeManager,
            $appState,
            $priceCurrency,
            $dbCompatibleMode
        );
    }

    /**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @param App\RequestInterface $request
     * @param App\ResponseInterface $response
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function loadValidOrder(App\RequestInterface $request, App\ResponseInterface $response)
    {
        if ($this->customerSession->isLoggedIn()) {
            $response->setRedirect($this->_urlBuilder->getUrl('sales/order/history'));
            return false;
        }

        $post = $request->getPost();
        $errors = false;

        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->orderFactory->create();

        $fromCookie = $this->cookieManager->getCookie(self::COOKIE_NAME);
        if (empty($post) && !$fromCookie) {
            $response->setRedirect($this->_urlBuilder->getUrl('sales/guest/form'));
            return false;
        } elseif (!empty($post) && isset($post['oar_order_id']) && isset($post['oar_type'])) {
            $type = $post['oar_type'];
            $incrementId = $post['oar_order_id'];
            $lastName = $post['oar_billing_lastname'];
            $email = $post['oar_email'];
            $zip = $post['oar_zip'];

            if (empty($incrementId) || empty($lastName) || empty($type) || !in_array(
                $type,
                ['email', 'zip']
            ) || $type == 'email' && empty($email) || $type == 'zip' && empty($zip)
            ) {
                $errors = true;
            }

            if (!$errors) {
                $order->loadByIncrementId($incrementId);
            }

            $errors = true;
            if ($order->getId()) {
                $billingAddress = $order->getBillingAddress();
                if (strtolower($lastName) == strtolower($billingAddress->getLastname()) &&
                    ($type == 'email' && strtolower($email) == strtolower($billingAddress->getEmail()) ||
                    $type == 'zip' && strtolower($zip) == strtolower($billingAddress->getPostcode()))
                ) {
                    $errors = false;
                }
            }

            if (!$errors) {
                $toCookie = base64_encode($order->getProtectCode() . ':' . $incrementId);
                $this->setGuestViewCookie($toCookie);
            }
        } elseif ($fromCookie) {
            $cookieData = explode(':', base64_decode($fromCookie));
            $protectCode = isset($cookieData[0]) ? $cookieData[0] : null;
            $incrementId = isset($cookieData[1]) ? $cookieData[1] : null;

            $errors = true;
            if (!empty($protectCode) && !empty($incrementId)) {
                $order->loadByIncrementId($incrementId);
                if ($order->getProtectCode() == $protectCode) {
                    // renew cookie
                    $this->setGuestViewCookie($fromCookie);
                    $errors = false;
                }
            }
        }

        if (!$errors && $order->getId()) {
            $this->coreRegistry->register('current_order', $order);
            return true;
        }

        $this->messageManager->addError(__('You entered incorrect data. Please try again.'));
        $response->setRedirect($this->_urlBuilder->getUrl('sales/guest/form'));
        return false;
    }

    /**
     * Get Breadcrumbs for current controller action
     *
     * @return void
     */
    public function getBreadcrumbs()
    {
        $breadcrumbs = $this->_view->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            ]
        );
        $breadcrumbs->addCrumb(
            'cms_page',
            ['label' => __('Order Information'), 'title' => __('Order Information')]
        );
    }

    /**
     * Set guest-view cookie
     *
     * @param string $cookieValue
     * @return void
     */
    private function setGuestViewCookie($cookieValue)
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath(self::COOKIE_PATH)
            ->setDuration(self::COOKIE_LIFETIME)
            ->setHttpOnly(true);
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $cookieValue, $metadata);
    }
}
