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
 * @package     Magento_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Direct Module
 */
namespace Magento\Paypal\Model;

class Direct extends \Magento\Payment\Model\Method\Cc
{
    /**
     * @var string
     */
    protected $_code  = \Magento\Paypal\Model\Config::METHOD_WPP_DIRECT;

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Payment\Info';

    /**#@+
     * Availability options
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = true;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment        = true;
    /**#@-*/

    /**
     * Website Payments Pro instance
     *
     * @var \Magento\Paypal\Model\Pro
     */
    protected $_pro;

    /**
     * Website Payments Pro instance type
     *
     * @var $_proType string
     */
    protected $_proType = 'Magento\Paypal\Model\Pro';

    /**
     * @var \Magento\Paypal\Model\Method\ProTypeFactory
     */
    protected $_proTypeFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_requestHttp;

    /**
     * @var \Magento\Paypal\Model\CartFactory
     */
    protected $_cartFactory;

    /**
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\App\ModuleListInterface $moduleList
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Centinel\Model\Service $centinelService
     * @param \Magento\Paypal\Model\Method\ProTypeFactory $proTypeFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\App\RequestInterface $requestHttp
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Core\Model\Logger $logger,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\App\ModuleListInterface $moduleList,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory,
        \Magento\Core\Model\LocaleInterface $locale,
        \Magento\Centinel\Model\Service $centinelService,
        \Magento\Paypal\Model\Method\ProTypeFactory $proTypeFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\UrlInterface $urlBuilder,
        \Magento\App\RequestInterface $requestHttp,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        array $data = array()
    ) {
        parent::__construct(
            $logger,
            $eventManager,
            $coreStoreConfig,
            $moduleList,
            $paymentData,
            $logAdapterFactory,
            $locale,
            $centinelService,
            $data
        );
        $this->_proTypeFactory = $proTypeFactory;
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_requestHttp = $requestHttp;
        $this->_cartFactory = $cartFactory;

        $proInstance = array_shift($data);
        if ($proInstance && ($proInstance instanceof \Magento\Paypal\Model\Pro)) {
            $this->_pro = $proInstance;
        } else {
            $this->_pro = $this->_proTypeFactory->create($this->_proType);
        }
        $this->_pro->setMethod($this->_code);
    }

    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param \Magento\Core\Model\Store|int $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        if (null === $store) {
            $store = $this->_storeManager->getStore()->getId();
        }
        $this->_pro->getConfig()->setStoreId(is_object($store) ? $store->getId() : $store);
        return $this;
    }

    /**
     * Whether method is available for specified currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->_pro->getConfig()->isCurrencyCodeSupported($currencyCode);
    }

    /**
     * Payment action getter compatible with payment model
     *
     * @see \Magento\Sales\Model\Payment::place()
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->_pro->getConfig()->getPaymentAction();
    }

    /**
     * Return available CC types for gateway based on merchant country
     *
     * @return string
     */
    public function getAllowedCcTypes()
    {
        $ccTypes = explode(',', $this->_pro->getConfig()->cctypes);
        $country = $this->_pro->getConfig()->getMerchantCountry();

        if ($country == 'GB') {
            $ccTypes = array_intersect(array('SM', 'SO', 'MC', 'DI', 'VI'), $ccTypes);
        } elseif ($country == 'CA') {
            $ccTypes = array_intersect(array('MC', 'VI'), $ccTypes);
        }
        return implode(',', $ccTypes);
    }

    /**
     * Check whether payment method can be used
     * @param \Magento\Sales\Model\Quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (parent::isAvailable($quote) && $this->_pro->getConfig()->isMethodAvailable()) {
            return true;
        }
        return false;
    }

    /**
     * Custom getter for payment configuration
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        $value = null;
        switch ($field)
        {
            case 'cctypes':
                $value = $this->getAllowedCcTypes();
                break;
            default:
                $value = $this->_pro->getConfig()->$field;
        }
        return $value;
    }

    /**
     * Authorize payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Direct
     */
    public function authorize(\Magento\Object $payment, $amount)
    {
        return $this->_placeOrder($payment, $amount);
    }

    /**
     * Void payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Paypal\Model\Direct
     */
    public function void(\Magento\Object $payment)
    {
        $this->_pro->void($payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Direct
     */
    public function capture(\Magento\Object $payment, $amount)
    {
        if (false === $this->_pro->capture($payment, $amount)) {
            $this->_placeOrder($payment, $amount);
        }
        return $this;
    }

    /**
     * Refund capture
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Direct
     */
    public function refund(\Magento\Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param \Magento\Object|\Magento\Sales\Model\Order\Payment $payment
     * @return \Magento\Paypal\Model\Direct
     */
    public function cancel(\Magento\Object $payment)
    {
        $this->void($payment);

        return $this;
    }

    /**
     * Whether payment can be reviewed
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function canReviewPayment(\Magento\Payment\Model\Info $payment)
    {
        return parent::canReviewPayment($payment) && $this->_pro->canReviewPayment($payment);
    }

    /**
     * Attempt to accept a pending payment
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function acceptPayment(\Magento\Payment\Model\Info $payment)
    {
        parent::acceptPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_ACCEPT);
    }

    /**
     * Attempt to deny a pending payment
     *
     * @param \Magento\Payment\Model\Info|\Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    public function denyPayment(\Magento\Payment\Model\Info $payment)
    {
        parent::denyPayment($payment);
        return $this->_pro->reviewPayment($payment, \Magento\Paypal\Model\Pro::PAYMENT_REVIEW_DENY);
    }

    /**
     * Set fallback API URL if not defined in configuration
     *
     * @return \Magento\Centinel\Model\Service
     */
    public function getCentinelValidator()
    {
        $validator = parent::getCentinelValidator();
        if (!$validator->getCustomApiEndpointUrl()) {
            $validator->setCustomApiEndpointUrl($this->_pro->getConfig()->centinelDefaultApiUrl);
        }
        return $validator;
    }

    /**
     * Fetch transaction details info
     *
     * @param \Magento\Payment\Model\Info $payment
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\Info $payment, $transactionId)
    {
        return $this->_pro->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * Place an order with authorization or capture action
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @param float $amount
     * @return \Magento\Paypal\Model\Direct
     */
    protected function _placeOrder(\Magento\Sales\Model\Order\Payment $payment, $amount)
    {
        $order = $payment->getOrder();
        $api = $this->_pro->getApi()
            ->setPaymentAction($this->_pro->getConfig()->paymentAction)
            ->setIpAddress($this->_requestHttp->getClientIp(false))
            ->setAmount($amount)
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setInvNum($order->getIncrementId())
            ->setEmail($order->getCustomerEmail())
            ->setNotifyUrl($this->_urlBuilder->getUrl('paypal/ipn/'))
            ->setCreditCardType($payment->getCcType())
            ->setCreditCardNumber($payment->getCcNumber())
            ->setCreditCardExpirationDate(
                $this->_getFormattedCcExpirationDate($payment->getCcExpMonth(), $payment->getCcExpYear())
            )
            ->setCreditCardCvv2($payment->getCcCid())
            ->setMaestroSoloIssueNumber($payment->getCcSsIssue());

        if ($payment->getCcSsStartMonth() && $payment->getCcSsStartYear()) {
            $year = sprintf('%02d', substr($payment->getCcSsStartYear(), -2, 2));
            $api->setMaestroSoloIssueDate(
                $this->_getFormattedCcExpirationDate($payment->getCcSsStartMonth(), $year)
            );
        }
        if ($this->getIsCentinelValidationEnabled()) {
            $this->getCentinelValidator()->exportCmpiData($api);
        }

        // add shipping and billing addresses
        if ($order->getIsVirtual()) {
            $api->setAddress($order->getBillingAddress())->setSuppressShipping(true);
        } else {
            $api->setAddress($order->getShippingAddress());
            $api->setBillingAddress($order->getBillingAddress());
        }

        // add line items
        $parameters = array('params' => array($order));
        $api->setPaypalCart($this->_cartFactory->create($parameters))
            ->setIsLineItemsEnabled($this->_pro->getConfig()->lineItemsEnabled);

        // call api and import transaction and other payment information
        $api->callDoDirectPayment();
        $this->_importResultToPayment($api, $payment);

        try {
            $api->callGetTransactionDetails();
        } catch (\Magento\Core\Exception $e) {
            // if we receive errors, but DoDirectPayment response is Success, then set Pending status for transaction
            $payment->setIsTransactionPending(true);
        }
        $this->_importResultToPayment($api, $payment);
        return $this;
    }

    /**
     * Format credit card expiration date based on month and year values
     * Format: mmyyyy
     *
     * @param string|int $month
     * @param string|int $year
     * @return string
     */
    protected function _getFormattedCcExpirationDate($month, $year)
    {
        return sprintf('%02d%02d', $month, $year);
    }

    /**
     * Import direct payment results to payment
     *
     * @param \Magento\Paypal\Model\Api\Nvp
     * @param \Magento\Sales\Model\Order\Payment
     */
    protected function _importResultToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(0);
        $this->_pro->importPaymentInfo($api, $payment);
    }

    /**
     * Check void availability
     *
     * @param   \Magento\Object $payment
     * @return  bool
     */
    public function canVoid(\Magento\Object $payment)
    {
        if ($payment instanceof \Magento\Sales\Model\Order\Invoice
            || $payment instanceof \Magento\Sales\Model\Order\Creditmemo
        ) {
            return false;
        }

        return $this->_canVoid;
    }
}
