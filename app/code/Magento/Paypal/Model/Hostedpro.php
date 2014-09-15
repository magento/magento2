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
 * Website Payments Pro Hosted Solution payment gateway model
 */
class Hostedpro extends \Magento\Paypal\Model\Direct
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
    protected $_code = \Magento\Paypal\Model\Config::METHOD_HOSTEDPRO;

    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Paypal\Block\Hosted\Pro\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Paypal\Block\Hosted\Pro\Info';

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
    protected $_hostedproRequestFactory;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Logger\AdapterFactory $logAdapterFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Centinel\Model\Service $centinelService
     * @param \Magento\Paypal\Model\ProFactory $proFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $requestHttp
     * @param \Magento\Paypal\Model\CartFactory $cartFactory
     * @param \Magento\Paypal\Model\Hostedpro\RequestFactory $hostedproRequestFactory
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Logger\AdapterFactory $logAdapterFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Centinel\Model\Service $centinelService,
        \Magento\Paypal\Model\ProFactory $proFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $requestHttp,
        \Magento\Paypal\Model\CartFactory $cartFactory,
        \Magento\Paypal\Model\Hostedpro\RequestFactory $hostedproRequestFactory,
        array $data = array()
    ) {
        $this->_hostedproRequestFactory = $hostedproRequestFactory;
        parent::__construct(
            $eventManager,
            $paymentData,
            $scopeConfig,
            $logAdapterFactory,
            $logger,
            $moduleList,
            $localeDate,
            $centinelService,
            $proFactory,
            $storeManager,
            $urlBuilder,
            $requestHttp,
            $cartFactory,
            $data
        );
    }

    /**
     * Return available CC types for gateway based on merchant country.
     * We do not have to check the availability of card types.
     *
     * @return true
     */
    public function getAllowedCcTypes()
    {
        return true;
    }

    /**
     * Return merchant country code from config,
     * use default country if it not specified in General settings
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
     * @param \Magento\Framework\Object $stateObject
     * @return void
     */
    public function initialize($paymentAction, $stateObject)
    {
        switch ($paymentAction) {
            case \Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH:
            case \Magento\Paypal\Model\Config::PAYMENT_ACTION_SALE:
                $payment = $this->getInfoInstance();
                $order = $payment->getOrder();
                $order->setCanSendNewEmailFlag(false);
                $payment->setAmountAuthorized($order->getTotalDue());
                $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

                $this->_setPaymentFormUrl($payment);

                $stateObject->setState(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
                $stateObject->setStatus('pending_payment');
                $stateObject->setIsNotified(false);
                break;
            default:
                break;
        }
    }

    /**
     * Sends API request to PayPal to get form URL, then sets this URL to $payment object.
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _setPaymentFormUrl(\Magento\Payment\Model\Info $payment)
    {
        $request = $this->_buildFormUrlRequest($payment);
        $response = $this->_sendFormUrlRequest($request);
        if ($response) {
            $payment->setAdditionalInformation('secure_form_url', $response);
        } else {
            throw new \Magento\Framework\Model\Exception('Cannot get secure form URL from PayPal');
        }
    }

    /**
     * Returns request object with needed data for API request to PayPal to get form URL.
     *
     * @param \Magento\Payment\Model\Info $payment
     * @return \Magento\Paypal\Model\Hostedpro\Request
     */
    protected function _buildFormUrlRequest(\Magento\Payment\Model\Info $payment)
    {
        $request = $this->_buildBasicRequest()->setOrder($payment->getOrder())->setPaymentMethod($this);

        return $request;
    }

    /**
     * Returns form URL from request to PayPal.
     *
     * @param \Magento\Paypal\Model\Hostedpro\Request $request
     * @return string|false
     */
    protected function _sendFormUrlRequest(\Magento\Paypal\Model\Hostedpro\Request $request)
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
    protected function _buildBasicRequest()
    {
        $request = $this->_hostedproRequestFactory->create()->setData(
            array(
                'METHOD' => self::BM_BUTTON_METHOD,
                'BUTTONCODE' => self::BM_BUTTON_CODE,
                'BUTTONTYPE' => self::BM_BUTTON_TYPE
            )
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
        return $this->_getUrl('paypal/hostedpro/return', $storeId);
    }

    /**
     * Get notify (IPN) URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getNotifyUrl($storeId = null)
    {
        return $this->_getUrl('paypal/ipn', $storeId, false);
    }

    /**
     * Get cancel URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getCancelUrl($storeId = null)
    {
        return $this->_getUrl('paypal/hostedpro/cancel', $storeId);
    }

    /**
     * Build URL for store
     *
     * @param string $path
     * @param int $storeId
     * @param bool|null $secure
     * @return string
     */
    protected function _getUrl($path, $storeId, $secure = null)
    {
        $store = $this->_storeManager->getStore($storeId);
        return $this->_urlBuilder->getUrl(
            $path,
            array("_store" => $store, "_secure" => is_null($secure) ? $store->isCurrentlySecure() : $secure)
        );
    }
}
