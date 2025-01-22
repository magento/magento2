<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Hostedpro\Request;
use Magento\Sales\Model\Order;

/**
 * Website Payments Pro Hosted Solution payment gateway model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Hostedpro extends Direct
{
    /**
     * Button code
     */
    const BM_BUTTON_CODE = 'TOKEN';

    /**
     * Button type
     */
    const BM_BUTTON_TYPE = 'PAYMENT';

    /**
     * Paypal API method name for button creation
     */
    const BM_BUTTON_METHOD = 'BMCreateButton';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = Config::METHOD_HOSTEDPRO;

    /**
     * @var string
     */
    protected $_formBlockType = \Magento\Paypal\Block\Hosted\Pro\Form::class;

    /**
     * @var string
     */
    protected $_infoBlockType = \Magento\Paypal\Block\Hosted\Pro\Info::class;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * @var \Magento\Paypal\Model\Hostedpro\RequestFactory
     */
    protected $hostedproRequestFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param ProFactory $proFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $requestHttp
     * @param CartFactory $cartFactory
     * @param Hostedpro\RequestFactory $hostedproRequestFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Paypal\Model\ProFactory $proFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $requestHttp,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Paypal\Model\Hostedpro\RequestFactory $hostedproRequestFactory,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->hostedproRequestFactory = $hostedproRequestFactory;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            $proFactory,
            $storeManager,
            $urlBuilder,
            $requestHttp,
            $cartFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Return available CC types for gateway based on merchant country.
     *
     * We do not have to check the availability of card types.
     *
     * @return true
     */
    public function getAllowedCcTypes()
    {
        return true;
    }

    /**
     * Return merchant country code from config
     *
     * Use default country if it not specified in General settings
     *
     * @return string
     */
    public function getMerchantCountry()
    {
        return $this->_pro->getConfig()->getMerchantCountry();
    }

    /**
     * Do not validate payment form using server methods
     *
     * @return true
     */
    public function validate()
    {
        return true;
    }

    /**
     * Instantiate state and set it to state object
     *
     * @param string $paymentAction
     * @param \Magento\Framework\DataObject $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case Config::PAYMENT_ACTION_AUTH:
            case Config::PAYMENT_ACTION_SALE:
                $payment = $this->getInfoInstance();
                /** @var \Magento\Sales\Model\Order $order */
                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);
                $payment->setAmountAuthorized($order->getTotalDue());
                $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

                $this->setPaymentFormUrl($payment);

                $stateObject->setState(Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * Sends API request to PayPal to get form URL, then sets this URL to $payment object.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setPaymentFormUrl(InfoInterface $payment)
    {
        $request = $this->buildFormUrlRequest($payment);
        $response = $this->sendFormUrlRequest($request);
        if ($response) {
            $payment->setAdditionalInformation('secure_form_url', $response);
        } else {
            throw new LocalizedException(__('Cannot get secure form URL from PayPal'));
        }
    }

    /**
     * Returns request object with needed data for API request to PayPal to get form URL.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \Magento\Paypal\Model\Hostedpro\Request
     */
    protected function buildFormUrlRequest(InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $request = $this->buildBasicRequest()->setOrder($order)->setPaymentMethod($this)->setAmount($order);

        return $request;
    }

    /**
     * Returns form URL from request to PayPal.
     *
     * @param \Magento\Paypal\Model\Hostedpro\Request $request
     * @return string|false
     */
    protected function sendFormUrlRequest(Request $request)
    {
        $api = $this->_pro->getApi();
        $response = $api->call(self::BM_BUTTON_METHOD, $request->getRequestData());

        if (!isset($response['EMAILLINK'])) {
            return false;
        }
        return $response['EMAILLINK'];
    }

    /**
     * Return request object with basic information
     *
     * @return \Magento\Paypal\Model\Hostedpro\Request
     */
    protected function buildBasicRequest()
    {
        $request = $this->hostedproRequestFactory->create()->setData(
            [
                'METHOD' => self::BM_BUTTON_METHOD,
                'BUTTONCODE' => self::BM_BUTTON_CODE,
                'BUTTONTYPE' => self::BM_BUTTON_TYPE,
            ]
        );
        return $request;
    }

    /**
     * Get return URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getReturnUrl($storeId = null)
    {
        $payment = $this->getInfoInstance();
        $urlRoute = $payment->getAdditionalInformation('return_url') ?? 'paypal/hostedpro/return';
        return $this->getUrl($urlRoute, $storeId);
    }

    /**
     * Get notify (IPN) URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getNotifyUrl($storeId = null)
    {
        return $this->getUrl('paypal/ipn', $storeId, false);
    }

    /**
     * Get cancel URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCancelUrl($storeId = null)
    {
        $payment = $this->getInfoInstance();
        $urlRoute = $payment->getAdditionalInformation('cancel_url') ?? 'paypal/hostedpro/cancel';
        return $this->getUrl($urlRoute, $storeId);
    }

    /**
     * Build URL for store
     *
     * @param string $path
     * @param int $storeId
     * @param bool|null $secure
     * @return string
     */
    protected function getUrl($path, $storeId, $secure = null)
    {
        $store = $this->_storeManager->getStore($storeId);
        return $this->_urlBuilder->getUrl(
            $path,
            ["_secure" => $secure === null ? $store->isCurrentlySecure() : $secure]
        );
    }
}
