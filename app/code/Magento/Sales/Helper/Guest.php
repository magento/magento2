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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales module base helper
 */
namespace Magento\Sales\Helper;

class Guest extends \Magento\Core\Helper\Data
{
    /**
     * Cookie params
     */
    protected $_cookieName  = 'guest-view';

    /**
     * @var int
     */
    protected $_lifeTime    = 600;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Core\Model\Cookie
     */
    protected $_coreCookie;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_coreApp;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_coreSession;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @param \Magento\Core\Helper\Context $context
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Http $coreHttp
     * @param \Magento\Core\Model\Config $config
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\StoreManager $storeManager
     * @param \Magento\Core\Model\Locale $locale
     * @param \Magento\Core\Model\Date $dateModel
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\Encryption $encryptor
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Core\Model\Cookie $coreCookie
     * @param \Magento\Core\Model\App $coreApp
     * @param \Magento\Core\Model\Session $coreSession
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param bool $dbCompatibleMode
     */
    public function __construct(
        \Magento\Core\Helper\Context $context,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Http $coreHttp,
        \Magento\Core\Model\Config $config,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\StoreManager $storeManager,
        \Magento\Core\Model\Locale $locale,
        \Magento\Core\Model\Date $dateModel,
        \Magento\App\State $appState,
        \Magento\Core\Model\Encryption $encryptor,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Core\Model\Cookie $coreCookie,
        \Magento\Core\Model\App $coreApp,
        \Magento\Core\Model\Session $coreSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        $dbCompatibleMode = true
    )
    {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_coreCookie = $coreCookie;
        $this->_coreApp = $coreApp;
        $this->_coreSession = $coreSession;
        $this->_orderFactory = $orderFactory;
        parent::__construct($context, $eventManager, $coreHttp, $config, $coreStoreConfig, $storeManager,
            $locale, $dateModel, $appState, $encryptor, $dbCompatibleMode
        );
    }

    /**
     * Try to load valid order by $_POST or $_COOKIE
     *
     * @return bool|null
     */
    public function loadValidOrder()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $this->_coreApp->getResponse()->setRedirect($this->_urlBuilder->getUrl('sales/order/history'));
            return false;
        }

        $post = $this->_coreApp->getRequest()->getPost();
        $errors = false;

        /** @var $order \Magento\Sales\Model\Order */
        $order = $this->_orderFactory->create();

        if (empty($post) && !$this->_coreCookie->get($this->_cookieName)) {
            $this->_coreApp->getResponse()->setRedirect($this->_urlBuilder->getUrl('sales/guest/form'));
            return false;
        } elseif (!empty($post) && isset($post['oar_order_id']) && isset($post['oar_type']))  {
            $type           = $post['oar_type'];
            $incrementId    = $post['oar_order_id'];
            $lastName       = $post['oar_billing_lastname'];
            $email          = $post['oar_email'];
            $zip            = $post['oar_zip'];

            if (empty($incrementId) || empty($lastName) || empty($type) || (!in_array($type, array('email', 'zip')))
                || ($type == 'email' && empty($email)) || ($type == 'zip' && empty($zip))) {
                $errors = true;
            }

            if (!$errors) {
                $order->loadByIncrementId($incrementId);
            }

            if ($order->getId()) {
                $billingAddress = $order->getBillingAddress();
                if ((strtolower($lastName) != strtolower($billingAddress->getLastname()))
                    || ($type == 'email'
                        && strtolower($email) != strtolower($billingAddress->getEmail()))
                    || ($type == 'zip'
                        && (strtolower($zip) != strtolower($billingAddress->getPostcode())))
                ) {
                    $errors = true;
                }
            } else {
                $errors = true;
            }

            if (!$errors) {
                $toCookie = base64_encode($order->getProtectCode());
                $this->_coreCookie->set($this->_cookieName, $toCookie, $this->_lifeTime, '/');
            }
        } elseif ($this->_coreCookie->get($this->_cookieName)) {
            $fromCookie     = $this->_coreCookie->get($this->_cookieName);
            $protectCode    = base64_decode($fromCookie);

            if (!empty($protectCode)) {
                $order->loadByAttribute('protect_code', $protectCode);

                $this->_coreCookie->renew($this->_cookieName, $this->_lifeTime, '/');
            } else {
                $errors = true;
            }
        }

        if (!$errors && $order->getId()) {
            $this->_coreRegistry->register('current_order', $order);
            return true;
        }

        $this->_coreSession->addError(
            __('You entered incorrect data. Please try again.')
        );
        $this->_coreApp->getResponse()->setRedirect($this->_urlBuilder->getUrl('sales/guest/form'));
        return false;
    }

    /**
     * Get Breadcrumbs for current controller action
     *
     * @param  \Magento\Core\Controller\Front\Action $controller
     */
    public function getBreadcrumbs($controller)
    {
        $breadcrumbs = $controller->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb(
            'home',
            array(
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link'  => $this->_storeManager->getStore()->getBaseUrl()
            )
        );
        $breadcrumbs->addCrumb(
            'cms_page',
            array(
                'label' => __('Order Information'),
                'title' => __('Order Information')
            )
        );
    }

}
