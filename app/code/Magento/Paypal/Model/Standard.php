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
namespace Magento\Paypal\Model;

/**
 * PayPal Standard Checkout Module
 */
class Standard extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = \Magento\Paypal\Model\Config::METHOD_WPS;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Standard\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payment\Info';

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Config instance
     *
     * @var \Magento\Paypal\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Paypal\Model\Session
     */
    protected $_paypalSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Paypal\Model\Api\StandardFactory
     */
    protected $_apiStandardFactory;

    /**
     * @var \Magento\Paypal\Model\CartFactory
     */
    protected $_cartFactory;

    /**
     * @var \Magento\Paypal\Model\Config\Factory
     */
    protected $_configFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Framework\Session\Generic $paypalSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Paypal\Model\Api\StandardFactory $apiStandardFactory
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param \Magento\Paypal\Model\Config\Factory $configFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Framework\Session\Generic $paypalSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Paypal\Model\Api\StandardFactory $apiStandardFactory,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Paypal\Model\Config\Factory $configFactory,
        array $data = array()
    ) {
        $this->_paypalSession = $paypalSession;
        $this->_checkoutSession = $checkoutSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->_orderFactory = $orderFactory;
        $this->_apiStandardFactory = $apiStandardFactory;
        $this->_cartFactory = $cartFactory;
        $this->_configFactory = $configFactory;
        parent::__construct($eventManager, $paymentData, $scopeConfig, $logAdapterFactory, $data);
    }

    /**
     * Whether method is available for specified currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->getConfig()->isCurrencyCodeSupported($currencyCode);
    }

    /**
     * Get paypal session namespace
     *
     * @return \Magento\Framework\Session\Generic
     */
    public function getSession()
    {
        return $this->_paypalSession;
    }

    /**
     * Get checkout session namespace
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckout()
    {
        return $this->_checkoutSession;
    }

    /**
     * Get current quote
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Create main block for standard form
     *
     * @param string $name
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock(
            'Magento\Paypal\Block\Standard\Form',
            $name
        )->setMethod(
            'paypal_standard'
        )->setPayment(
            $this->getPayment()
        )->setTemplate(
            'standard/form.phtml'
        );

        return $block;
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('paypal/standard/redirect', array('_secure' => true));
    }

    /**
     * Return form field array
     *
     * @return array
     */
    public function getStandardCheckoutFormFields()
    {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = $this->_orderFactory->create()->loadByIncrementId($orderIncrementId);
        /* @var $api \Magento\Paypal\Model\Api\Standard */
        $api = $this->_apiStandardFactory->create()->setConfigObject($this->getConfig());
        $api->setOrderId(
            $orderIncrementId
        )->setCurrencyCode($order->getBaseCurrencyCode())
        //->setPaymentAction()
        ->setOrder(
            $order
        )->setNotifyUrl(
            $this->_urlBuilder->getUrl('paypal/ipn/')
        )->setReturnUrl(
            $this->_urlBuilder->getUrl('paypal/standard/success')
        )->setCancelUrl(
            $this->_urlBuilder->getUrl('paypal/standard/cancel')
        );

        // export address
        $isOrderVirtual = $order->getIsVirtual();
        $address = $isOrderVirtual ? $order->getBillingAddress() : $order->getShippingAddress();
        if ($isOrderVirtual) {
            $api->setNoShipping(true);
        } elseif ($address->validate()) {
            $api->setAddress($address);
        }

        // add cart totals and line items
        $cart = $this->_cartFactory->create(array('salesModel' => $order));
        $api->setPaypalCart($cart)->setIsLineItemsEnabled($this->_config->getConfigValue('lineItemsEnabled'));
        $api->setCartSummary($this->_getAggregatedCartSummary());
        $api->setLocale($api->getLocaleCode());
        $result = $api->getStandardCheckoutRequest();
        return $result;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param object $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
    }

    /**
     * Config instance getter
     * @return \Magento\Paypal\Model\Config
     */
    public function getConfig()
    {
        if (null === $this->_config) {
            $params = array($this->_code);
            $store = $this->getStore();
            if ($store) {
                $params[] = is_object($store) ? $store->getId() : $store;
            }
            $this->_config = $this->_configFactory->create('Magento\Paypal\Model\Config', array('params' => $params));
        }
        return $this->_config;
    }

    /**
     * Check whether payment method can be used
     * @param \Magento\Sales\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (parent::isAvailable($quote) && $this->getConfig()->isMethodAvailable()) {
            return true;
        }
        return false;
    }

    /**
     * Custom getter for payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->getConfig()->getConfigValue($field);
    }

    /**
     * Aggregated cart summary label getter
     *
     * @return string
     */
    private function _getAggregatedCartSummary()
    {
        if ($this->_config->getConfigValue('lineItemsSummary')) {
            return $this->_config->getConfigValue('lineItemsSummary');
        }
        return $this->_storeManager->getStore($this->getStore())->getFrontendName();
    }
}
