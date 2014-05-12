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
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Stdlib\Cookie
     */
    protected $_coreCookie;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

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
     * @param \Magento\Framework\Stdlib\Cookie $coreCookie
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Framework\App\ViewInterface $view
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        App\Helper\Context $context,
        App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\Cookie $coreCookie,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\ViewInterface $view,
        $dbCompatibleMode = true
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_coreCookie = $coreCookie;
        $this->messageManager = $messageManager;
        $this->_orderFactory = $orderFactory;
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
     */
    public function loadValidOrder(App\RequestInterface $request, App\ResponseInterface $response)
    {
        if ($this->_customerSession->isLoggedIn()) {
            $response->setRedirect($this->_urlBuilder->getUrl('sales/order/history'));
            return false;
        }

        $post = $request->getPost();
        $errors = false;

        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->_orderFactory->create();

        if (empty($post) && !$this->_coreCookie->get(self::COOKIE_NAME)) {
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
                array('email', 'zip')
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
                $this->_coreCookie->set(self::COOKIE_NAME, $toCookie, self::COOKIE_LIFETIME, self::COOKIE_PATH);
            }
        } elseif ($this->_coreCookie->get(self::COOKIE_NAME)) {
            $fromCookie = $this->_coreCookie->get(self::COOKIE_NAME);
            $cookieData = explode(':', base64_decode($fromCookie));
            $protectCode = isset($cookieData[0]) ? $cookieData[0] : null;
            $incrementId = isset($cookieData[1]) ? $cookieData[1] : null;

            $errors = true;
            if (!empty($protectCode) && !empty($incrementId)) {
                $order->loadByIncrementId($incrementId);
                if ($order->getProtectCode() == $protectCode) {
                    $this->_coreCookie->renew(self::COOKIE_NAME, self::COOKIE_LIFETIME, self::COOKIE_PATH);
                    $errors = false;
                }
            }
        }

        if (!$errors && $order->getId()) {
            $this->_coreRegistry->register('current_order', $order);
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
            array(
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
            )
        );
        $breadcrumbs->addCrumb(
            'cms_page',
            array('label' => __('Order Information'), 'title' => __('Order Information'))
        );
    }
}
