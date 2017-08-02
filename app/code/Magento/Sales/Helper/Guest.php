<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Helper;

use Magento\Framework\App as App;

/**
 * Sales module base helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Guest extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     * @since 2.0.0
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     * @since 2.0.0
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     * @since 2.0.0
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     * @since 2.0.0
     */
    protected $resultRedirectFactory;

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
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    private $_storeManager;

    /**
     * @param App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->messageManager = $messageManager;
        $this->orderFactory = $orderFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;

        parent::__construct(
            $context
        );
    }

    /**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @param App\RequestInterface $request
     * @return \Magento\Framework\Controller\Result\Redirect|bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
     */
    public function loadValidOrder(App\RequestInterface $request)
    {
        if ($this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('sales/order/history');
        }

        $post = $request->getPostValue();
        $errors = false;

        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->orderFactory->create();

        $fromCookie = $this->cookieManager->getCookie(self::COOKIE_NAME);
        if (empty($post) && !$fromCookie) {
            return $this->resultRedirectFactory->create()->setPath('sales/guest/form');
        } elseif (!empty($post) && isset($post['oar_order_id']) && isset($post['oar_type'])) {
            $type = $post['oar_type'];
            $incrementId = $post['oar_order_id'];
            $lastName = $post['oar_billing_lastname'];
            $email = $post['oar_email'];
            $zip = $post['oar_zip'];
            $storeId = $this->_storeManager->getStore()->getId();

            if (empty($incrementId) || empty($lastName) || empty($type) || empty($storeId) || !in_array(
                $type,
                ['email', 'zip']
            ) || $type == 'email' && empty($email) || $type == 'zip' && empty($zip)
            ) {
                $errors = true;
            }

            if (!$errors) {
                $order = $order->loadByIncrementIdAndStoreId($incrementId, $storeId);
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
                if ($order->getProtectCode() === $protectCode) {
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
        return $this->resultRedirectFactory->create()->setPath('sales/guest/form');
    }

    /**
     * Get Breadcrumbs for current controller action
     *
     * @param \Magento\Framework\View\Result\Page $resultPage
     * @return void
     * @since 2.0.0
     */
    public function getBreadcrumbs(\Magento\Framework\View\Result\Page $resultPage)
    {
        $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
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
     * @since 2.0.0
     */
    private function setGuestViewCookie($cookieValue)
    {
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath(self::COOKIE_PATH)
            ->setHttpOnly(true);
        $this->cookieManager->setPublicCookie(self::COOKIE_NAME, $cookieValue, $metadata);
    }
}
