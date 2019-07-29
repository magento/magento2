<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Paypal\Model;

use Magento\Payment\Helper\Formatter;

/**
 * Config model that is aware of all \Magento\Paypal payment methods
 *
 * Works with PayPal-specific system configuration

 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Config extends AbstractConfig
{

    use Formatter;

    /**
     * PayPal Express
     */
    const METHOD_EXPRESS = 'paypal_express';

    /**
     * PayPal Standard - alias METHOD_WPP_EXPRESS
     */
    const METHOD_WPS_EXPRESS = 'wps_express';

    /**
     * PayPal Standard Bml - alias METHOD_WPP_BML
     */
    const METHOD_WPS_BML = 'wps_express_bml';

    /**
     * PayPal Bill Me Later - Express Checkout
     */
    const METHOD_WPP_BML = 'paypal_express_bml';

    /**
     * PayPal Website Payments Pro - Direct Payments
     */
    const METHOD_WPP_DIRECT = 'paypal_direct';

    /**
     * PayPal Website Payments Pro - Direct Payments - alias METHOD_PAYFLOWPRO
     */
    const METHOD_PAYMENT_PRO = 'paypal_payment_pro';

    /**
     * Express Checkout (Payflow Edition)
     */
    const METHOD_WPP_PE_EXPRESS = 'payflow_express';

    /**
     * PayPal Bill Me Later - Express Checkout (Payflow Edition)
     */
    const METHOD_WPP_PE_BML = 'payflow_express_bml';

    /**
     * Payflow Pro Gateway
     */
    const METHOD_PAYFLOWPRO = 'payflowpro';

    const METHOD_PAYFLOWLINK = 'payflow_link';

    const METHOD_PAYFLOWADVANCED = 'payflow_advanced';

    const METHOD_HOSTEDPRO = 'hosted_pro';

    const METHOD_BILLING_AGREEMENT = 'paypal_billing_agreement';

    /**#@+
     * Buttons and images
     */
    const EC_FLAVOR_DYNAMIC = 'dynamic';

    const EC_FLAVOR_STATIC = 'static';

    const EC_BUTTON_TYPE_SHORTCUT = 'ecshortcut';

    const EC_BUTTON_TYPE_MARK = 'ecmark';

    const PAYMENT_MARK_SMALL = 'small';

    const PAYMENT_MARK_MEDIUM = 'medium';

    const PAYMENT_MARK_LARGE = 'large';

    /**#@-*/
    const DEFAULT_LOGO_TYPE = 'wePrefer_150x60';

    /**#@+
     * Payment actions
     */
    const AUTHORIZATION_AMOUNT_ONE = 1;

    const AUTHORIZATION_AMOUNT_FULL = 2;

    /**#@-*/

    /**#@+
     * Require Billing Address
     */
    const REQUIRE_BILLING_ADDRESS_NO = 0;

    const REQUIRE_BILLING_ADDRESS_ALL = 1;

    const REQUIRE_BILLING_ADDRESS_VIRTUAL = 2;

    /**#@-*/

    /**#@+
     * Fraud management actions
     */
    const FRAUD_ACTION_ACCEPT = 'Acept';

    const FRAUD_ACTION_DENY = 'Deny';

    /**#@-*/

    /**#@+
     * Refund types
     */
    const REFUND_TYPE_FULL = 'Full';

    const REFUND_TYPE_PARTIAL = 'Partial';

    /**#@-*/

    /**#@+
     * Express Checkout flows
     */
    const EC_SOLUTION_TYPE_SOLE = 'Sole';

    const EC_SOLUTION_TYPE_MARK = 'Mark';

    /**#@-*/

    /**#@+
     * Payment data transfer methods (Standard)
     */
    const WPS_TRANSPORT_IPN = 'ipn';

    const WPS_TRANSPORT_PDT = 'pdt';

    const WPS_TRANSPORT_IPN_PDT = 'ipn_n_pdt';

    /**#@-*/

    /**#@+
     * Billing Agreement Signup type
     */
    const EC_BA_SIGNUP_AUTO = 'auto';

    const EC_BA_SIGNUP_ASK = 'ask';

    const EC_BA_SIGNUP_NEVER = 'never';

    /**
     * Paypal setting
     */
    const TRANSFER_CART_LINE_ITEMS = 'lineItemsEnabled';
    const TRANSFER_SHIPPING_OPTIONS = 'transferShippingOptions';

    /**#@-*/

    /**
     * Config path for enabling/disabling order review step in express checkout
     */
    const XML_PATH_PAYPAL_EXPRESS_SKIP_ORDER_REVIEW_STEP_FLAG = 'payment/paypal_express/skip_order_review_step';

    /**
     * Instructions for generating proper BN code
     *
     * @var array
     */
    protected $_buildNotationPPMap = [
        'paypal_express' => 'EC',
        'paypal_direct' => 'DP',
        'payflow_express' => 'EC',
    ];

    /**
     * Style system config map (Express Checkout)
     *
     * @var array
     */
    protected $_ecStyleConfigMap = [
        'page_style' => 'page_style',
        'paypal_hdrimg' => 'hdrimg',
        'paypal_hdrbordercolor' => 'hdrbordercolor',
        'paypal_hdrbackcolor' => 'hdrbackcolor',
        'paypal_payflowcolor' => 'payflowcolor',
    ];

    /**
     * Currency codes supported by PayPal methods
     *
     * @var string[]
     */
    protected $_supportedCurrencyCodes = [
        'AUD',
        'CAD',
        'CZK',
        'DKK',
        'EUR',
        'HKD',
        'HUF',
        'ILS',
        'JPY',
        'MXN',
        'NOK',
        'NZD',
        'PLN',
        'GBP',
        'RUB',
        'SGD',
        'SEK',
        'CHF',
        'TWD',
        'THB',
        'USD',
        'INR',
    ];

    /**
     * Merchant country supported by PayPal
     *
     * @var string[]
     */
    protected $_supportedCountryCodes = [
        'AE',
        'AR',
        'AT',
        'AU',
        'BE',
        'BG',
        'BR',
        'CA',
        'CN',
        'CH',
        'CL',
        'CR',
        'CY',
        'CZ',
        'DE',
        'DK',
        'DO',
        'EC',
        'EE',
        'ES',
        'FI',
        'FR',
        'GB',
        'GF',
        'GI',
        'GP',
        'GR',
        'HK',
        'HU',
        'ID',
        'IE',
        'IL',
        'IN',
        'IS',
        'IT',
        'JM',
        'JP',
        'KR',
        'LI',
        'LT',
        'LU',
        'LV',
        'MQ',
        'MT',
        'MX',
        'MY',
        'NL',
        'NO',
        'NZ',
        'PH',
        'PL',
        'PT',
        'RU',
        'RE',
        'RO',
        'SE',
        'SG',
        'SI',
        'SK',
        'SM',
        'TH',
        'TR',
        'TW',
        'US',
        'UY',
        'VE',
        'VN',
        'ZA',
    ];

    /**
     * Buyer country supported by PayPal
     *
     * @var string[]
     */
    protected $_supportedBuyerCountryCodes = [
        'AF ',
        'AX ',
        'AL ',
        'DZ ',
        'AS ',
        'AD ',
        'AO ',
        'AI ',
        'AQ ',
        'AG ',
        'AR ',
        'AM ',
        'AW ',
        'AU ',
        'AT ',
        'AZ ',
        'BS ',
        'BH ',
        'BD ',
        'BB ',
        'BY ',
        'BE ',
        'BZ ',
        'BJ ',
        'BM ',
        'BT ',
        'BO ',
        'BA ',
        'BW ',
        'BV ',
        'BR ',
        'IO ',
        'BN ',
        'BG ',
        'BF ',
        'BI ',
        'KH ',
        'CM ',
        'CA ',
        'CV ',
        'KY ',
        'CF ',
        'TD ',
        'CL ',
        'CN ',
        'CX ',
        'CC ',
        'CO ',
        'KM ',
        'CG ',
        'CD ',
        'CK ',
        'CR ',
        'CI ',
        'HR ',
        'CU ',
        'CY ',
        'CZ ',
        'DK ',
        'DJ ',
        'DM ',
        'DO ',
        'EC ',
        'EG ',
        'SV ',
        'GQ ',
        'ER ',
        'EE ',
        'ET ',
        'FK ',
        'FO ',
        'FJ ',
        'FI ',
        'FR ',
        'GF ',
        'PF ',
        'TF ',
        'GA ',
        'GM ',
        'GE ',
        'DE ',
        'GH ',
        'GI ',
        'GR ',
        'GL ',
        'GD ',
        'GP ',
        'GU ',
        'GT ',
        'GG ',
        'GN ',
        'GW ',
        'GY ',
        'HT ',
        'HM ',
        'VA ',
        'HN ',
        'HK ',
        'HU ',
        'IS ',
        'IN ',
        'ID ',
        'IR ',
        'IQ ',
        'IE ',
        'IM ',
        'IL ',
        'IT ',
        'JM ',
        'JP ',
        'JE ',
        'JO ',
        'KZ ',
        'KE ',
        'KI ',
        'KP ',
        'KR ',
        'KW ',
        'KG ',
        'LA ',
        'LV ',
        'LB ',
        'LS ',
        'LR ',
        'LY ',
        'LI ',
        'LT ',
        'LU ',
        'MO ',
        'MK ',
        'MG ',
        'MW ',
        'MY ',
        'MV ',
        'ML ',
        'MT ',
        'MH ',
        'MQ ',
        'MR ',
        'MU ',
        'YT ',
        'MX ',
        'FM ',
        'MD ',
        'MC ',
        'MN ',
        'MS ',
        'MA ',
        'MZ ',
        'MM ',
        'NA ',
        'NR ',
        'NP ',
        'NL ',
        'AN ',
        'NC ',
        'NZ ',
        'NI ',
        'NE ',
        'NG ',
        'NU ',
        'NF ',
        'MP ',
        'NO ',
        'OM ',
        'PK ',
        'PW ',
        'PS ',
        'PA ',
        'PG ',
        'PY ',
        'PE ',
        'PH ',
        'PN ',
        'PL ',
        'PT ',
        'PR ',
        'QA ',
        'RE ',
        'RO ',
        'RU ',
        'RW ',
        'SH ',
        'KN ',
        'LC ',
        'PM ',
        'VC ',
        'WS ',
        'SM ',
        'ST ',
        'SA ',
        'SN ',
        'CS ',
        'SC ',
        'SL ',
        'SG ',
        'SK ',
        'SI ',
        'SB ',
        'SO ',
        'ZA ',
        'GS ',
        'ES ',
        'LK ',
        'SD ',
        'SR ',
        'SJ ',
        'SZ ',
        'SE ',
        'CH ',
        'SY ',
        'TW ',
        'TJ ',
        'TZ ',
        'TH ',
        'TL ',
        'TG ',
        'TK ',
        'TO ',
        'TT ',
        'TN ',
        'TR ',
        'TM ',
        'TC ',
        'TV ',
        'UG ',
        'UA ',
        'AE ',
        'GB ',
        'US ',
        'UM ',
        'UY ',
        'UZ ',
        'VU ',
        'VE ',
        'VN ',
        'VG ',
        'VI ',
        'WF ',
        'EH ',
        'YE ',
        'ZM ',
        'ZW',
    ];

    /**
     * Locale codes supported by misc images (marks, shortcuts etc)
     *
     * @var string[]
     * @link https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_ECButtonIntegration#id089QD0O0TX4__id08AH904I0YK
     */
    protected $_supportedImageLocales = [
        'de_DE',
        'en_AU',
        'en_GB',
        'en_US',
        'es_ES',
        'es_XC',
        'fr_FR',
        'fr_XC',
        'it_IT',
        'ja_JP',
        'nl_NL',
        'pl_PL',
        'zh_CN',
        'zh_XC',
    ];

    /**
     * Core data
     *
     * @var \Magento\Directory\Helper\Data
     */
    protected $directoryHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Payment\Model\Source\CctypeFactory
     */
    protected $_cctypeFactory;

    /**
     * @var \Magento\Paypal\Model\CertFactory
     */
    protected $_certFactory;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Payment\Model\Source\CctypeFactory $cctypeFactory
     * @param CertFactory $certFactory
     * @param array $params
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Model\Source\CctypeFactory $cctypeFactory,
        \Magento\Paypal\Model\CertFactory $certFactory,
        $params = []
    ) {
        parent::__construct($scopeConfig);
        $this->directoryHelper = $directoryHelper;
        $this->_storeManager = $storeManager;
        $this->_cctypeFactory = $cctypeFactory;
        $this->_certFactory = $certFactory;
        if ($params) {
            $method = array_shift($params);
            $this->setMethod($method);
            if ($params) {
                $storeId = array_shift($params);
                $this->setStoreId($storeId);
            }
        }
    }

    /**
     * Check whether method available for checkout or not
     *
     * Logic based on merchant country, methods dependence
     *
     * @param string|null $methodCode
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function isMethodAvailable($methodCode = null)
    {
        $result = parent::isMethodAvailable($methodCode);

        switch ($methodCode) {
            case self::METHOD_WPP_EXPRESS:
            case self::METHOD_WPS_EXPRESS:
                if ($this->isMethodActive(self::METHOD_PAYFLOWPRO)
                    || $this->isMethodActive(self::METHOD_PAYMENT_PRO)
                ) {
                    $result = true;
                }
                break;
            case self::METHOD_WPP_BML:
            case self::METHOD_WPS_BML:
                // check for express payments dependence
                if (!$this->isMethodActive(self::METHOD_WPP_EXPRESS)
                    && !$this->isMethodActive(self::METHOD_WPS_EXPRESS)
                ) {
                    $result = false;
                }
                break;
            case self::METHOD_WPP_PE_EXPRESS:
                // check for direct payments dependence
                if ($this->isMethodActive(self::METHOD_PAYFLOWLINK)
                    || $this->isMethodActive(self::METHOD_PAYFLOWADVANCED)
                ) {
                    $result = true;
                } elseif (!$this->isMethodActive(self::METHOD_PAYFLOWPRO)) {
                    $result = false;
                }
                break;
            case self::METHOD_WPP_PE_BML:
                // check for express payments dependence
                if (!$this->isMethodActive(self::METHOD_WPP_PE_EXPRESS)) {
                    $result = false;
                }
                break;
            case self::METHOD_BILLING_AGREEMENT:
                $result = $this->isWppApiAvailable();
                break;
        }
        return $result;
    }

    /**
     * Return merchant country codes supported by PayPal
     *
     * @return string[]
     */
    public function getSupportedMerchantCountryCodes()
    {
        return $this->_supportedCountryCodes;
    }

    /**
     * Return buyer country codes supported by PayPal
     *
     * @return string[]
     */
    public function getSupportedBuyerCountryCodes()
    {
        return $this->_supportedBuyerCountryCodes;
    }

    /**
     * Return merchant country code, use default country if it not specified in General settings
     *
     * @return string
     */
    public function getMerchantCountry()
    {
        $countryCode = $this->_scopeConfig->getValue(
            $this->_mapGeneralFieldset('merchant_country'),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
        if (!$countryCode) {
            $countryCode = $this->directoryHelper->getDefaultCountry($this->_storeId);
        }
        return $countryCode;
    }

    /**
     * Check whether method supported for specified country or not
     *
     * Use $_methodCode and merchant country by default
     *
     * @param string|null $method
     * @param string|null $countryCode
     * @return bool
     */
    public function isMethodSupportedForCountry($method = null, $countryCode = null)
    {
        if ($method === null) {
            $method = $this->getMethodCode();
        }
        if ($countryCode === null) {
            $countryCode = $this->getMerchantCountry();
        }
        return in_array($method, $this->getCountryMethods($countryCode));
    }

    /**
     * Return list of allowed methods for specified country iso code
     *
     * @param string|null $countryCode 2-letters iso code
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCountryMethods($countryCode = null)
    {
        $countryMethods = [
            'other' => [
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'US' => [
                self::METHOD_PAYFLOWADVANCED,
                self::METHOD_PAYFLOWPRO,
                self::METHOD_PAYFLOWLINK,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_WPP_BML,
                self::METHOD_BILLING_AGREEMENT,
                self::METHOD_WPP_PE_EXPRESS,
                self::METHOD_WPP_PE_BML,
            ],
            'CA' => [
                self::METHOD_PAYFLOWPRO,
                self::METHOD_PAYFLOWLINK,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
                self::METHOD_WPP_PE_EXPRESS,
            ],
            'GB' => [
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'AU' => [
                self::METHOD_PAYFLOWPRO,
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'NZ' => [
                self::METHOD_PAYFLOWPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'JP' => [
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'FR' => [
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'IT' => [
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'ES' => [
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'HK' => [
                self::METHOD_HOSTEDPRO,
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
            'DE' => [
                self::METHOD_WPP_EXPRESS,
                self::METHOD_BILLING_AGREEMENT,
            ],
        ];
        if ($countryCode === null) {
            return $countryMethods;
        }
        return isset($countryMethods[$countryCode]) ? $countryMethods[$countryCode] : $countryMethods['other'];
    }

    /**
     * Return start url for PayPal Basic
     *
     * @param string $token
     * @return string
     */
    public function getPayPalBasicStartUrl($token)
    {
        $params = [
            'cmd'   => '_express-checkout',
            'token' => $token,
        ];

        if ($this->isOrderReviewStepDisabled()) {
            $params['useraction'] = 'commit';
        }

        return $this->getPaypalUrl($params);
    }

    /**
     * Check whether order review step enabled in configuration
     *
     * @return bool
     */
    public function isOrderReviewStepDisabled()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_PAYPAL_EXPRESS_SKIP_ORDER_REVIEW_STEP_FLAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Get url for dispatching customer to express checkout start
     *
     * @param string $token
     * @return string
     */
    public function getExpressCheckoutStartUrl($token)
    {
        return sprintf(
            'https://www.%spaypal.com/checkoutnow%s',
            $this->getValue('sandboxFlag') ? 'sandbox.' : '',
            '?token=' . urlencode($token)
        );
    }

    /**
     * Get url for dispatching customer to checkout retrial
     *
     * @param string $orderId
     * @return string
     */
    public function getExpressCheckoutOrderUrl($orderId)
    {
        return $this->getPaypalUrl(['cmd' => '_express-checkout', 'order_id' => $orderId]);
    }

    /**
     * Get url that allows to edit checkout details on paypal side
     *
     * @param \Magento\Paypal\Controller\Express|string $token
     * @return string
     */
    public function getExpressCheckoutEditUrl($token)
    {
        return $this->getPaypalUrl(['cmd' => '_express-checkout', 'useraction' => 'continue', 'token' => $token]);
    }

    /**
     * Get url for additional actions that PayPal may require customer to do after placing the order.
     *
     * For instance, redirecting customer to bank for payment confirmation.
     *
     * @param string $token
     * @return string
     */
    public function getExpressCheckoutCompleteUrl($token)
    {
        return $this->getPaypalUrl(['cmd' => '_complete-express-checkout', 'token' => $token]);
    }

    /**
     * Retrieve url for initialization of billing agreement
     *
     * @param string $token
     * @return string
     */
    public function getStartBillingAgreementUrl($token)
    {
        return $this->getPaypalUrl(['cmd' => '_customer-billing-agreement', 'token' => $token]);
    }

    /**
     * PayPal web URL generic getter
     *
     * @param array $params
     * @return string
     */
    public function getPaypalUrl(array $params = [])
    {
        return sprintf(
            'https://www.%spaypal.com/cgi-bin/webscr%s',
            $this->getValue('sandboxFlag') ? 'sandbox.' : '',
            $params ? '?' . http_build_query($params) : ''
        );
    }

    /**
     * PayPal web URL for IPN
     *
     * @return string
     */
    public function getPayPalIpnUrl()
    {
        return sprintf(
            'https://ipnpb.%spaypal.com/cgi-bin/webscr',
            $this->getValue('sandboxFlag') ? 'sandbox.' : ''
        );
    }

    /**
     * Whether Express Checkout button should be rendered dynamically
     *
     * @return bool
     */
    public function areButtonsDynamic()
    {
        return $this->getValue('buttonFlavor') === self::EC_FLAVOR_DYNAMIC;
    }

    /**
     * Express checkout shortcut pic URL getter
     *
     * PayPal will ignore "pal", if there is no total amount specified
     *
     * @param string $localeCode
     * @param float|null $orderTotal
     * @param string|null $pal encrypted summary about merchant
     * @return string
     * @see Paypal_Model_Api_Nvp::callGetPalDetails()
     */
    public function getExpressCheckoutShortcutImageUrl($localeCode, $orderTotal = null, $pal = null)
    {
        if ($this->areButtonsDynamic()) {
            return $this->_getDynamicImageUrl(self::EC_BUTTON_TYPE_SHORTCUT, $localeCode, $orderTotal, $pal);
        }
        if ($this->getValue('buttonType') === self::EC_BUTTON_TYPE_MARK) {
            return $this->getPaymentMarkImageUrl($localeCode);
        }

        return $this->getExpressCheckoutInContextImageUrl($localeCode);
    }

    /**
     * Express in context checkout shortcut pic URL getter
     *
     * @param string $localeCode
     * @return string
     */
    public function getExpressCheckoutInContextImageUrl($localeCode)
    {
        $localeCode = $this->_getSupportedLocaleCode($localeCode);

        if ($localeCode === 'en_US') {
            return 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-medium.png';
        }

        return sprintf('https://www.paypal.com/%s/i/btn/btn_xpressCheckout.gif', $localeCode);
    }

    /**
     * Get PayPal "mark" image URL
     *
     * Supposed to be used on payment methods selection
     * $staticSize is applicable for static images only
     *
     * @param string $localeCode
     * @param float|null $orderTotal
     * @param string|null $pal
     * @param string|null $staticSize
     * @return string
     */
    public function getPaymentMarkImageUrl($localeCode, $orderTotal = null, $pal = null, $staticSize = null)
    {
        if ($this->areButtonsDynamic()) {
            return $this->_getDynamicImageUrl(self::EC_BUTTON_TYPE_MARK, $localeCode, $orderTotal, $pal);
        }

        if (null === $staticSize) {
            $staticSize = $this->getValue('paymentMarkSize');
        }

        switch ($staticSize) {
            case self::PAYMENT_MARK_SMALL:
            case self::PAYMENT_MARK_MEDIUM:
            case self::PAYMENT_MARK_LARGE:
                break;
            default:
                $staticSize = self::PAYMENT_MARK_MEDIUM;
        }

        return sprintf(
            'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-%s.png',
            $staticSize
        );
    }

    /**
     * Get "What Is PayPal" localized URL
     *
     * Supposed to be used with "mark" as popup window
     *
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @return string
     */
    public function getPaymentMarkWhatIsPaypalUrl(\Magento\Framework\Locale\ResolverInterface $localeResolver = null)
    {
        $countryCode = 'US';
        if (null !== $localeResolver) {
            $shouldEmulate = null !== $this->_storeId && $this->_storeManager->getStore()->getId() != $this->_storeId;
            if ($shouldEmulate) {
                $localeResolver->emulate($this->_storeId);
            }
            $countryCode = \Locale::getRegion($localeResolver->getLocale());
            if ($shouldEmulate) {
                $localeResolver->revert();
            }
        }
        return sprintf(
            'https://www.paypal.com/%s/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside',
            strtolower($countryCode)
        );
    }

    /**
     * Getter for Solution banner images
     *
     * @param string $localeCode
     * @param bool $isVertical
     * @param bool $isEcheck
     * @return string
     */
    public function getSolutionImageUrl($localeCode, $isVertical = false, $isEcheck = false)
    {
        return sprintf(
            'https://www.paypal.com/%s/i/bnr/%s_solution_PP%s.gif',
            $this->_getSupportedLocaleCode($localeCode),
            $isVertical ? 'vertical' : 'horizontal',
            $isEcheck ? 'eCheck' : ''
        );
    }

    /**
     * Getter for Payment form logo images
     *
     * @param string $localeCode
     * @return string
     */
    public function getPaymentFormLogoUrl($localeCode)
    {
        $locale = $this->_getSupportedLocaleCode($localeCode);

        $imageType = 'logo';
        $domain = 'paypal.com';
        list(, $country) = explode('_', $locale);
        $countryPrefix = $country . '/';

        switch ($locale) {
            case 'en_GB':
                $imageName = 'horizontal_solution_PP';
                $imageType = 'bnr';
                $countryPrefix = '';
                break;
            case 'de_DE':
                $imageName = 'lockbox_150x47';
                break;
            case 'fr_FR':
                $imageName = 'bnr_horizontal_solution_PP_327wx80h';
                $imageType = 'bnr';
                $locale = 'en_US';
                $domain = 'paypalobjects.com';
                break;
            case 'it_IT':
                $imageName = 'bnr_horizontal_solution_PP_178wx80h';
                $imageType = 'bnr';
                $domain = 'paypalobjects.com';
                break;
            default:
                $imageName = 'PayPal_mark_60x38';
                $countryPrefix = '';
                break;
        }
        return sprintf('https://www.%s/%s/%si/%s/%s.gif', $domain, $locale, $countryPrefix, $imageType, $imageName);
    }

    /**
     * Return supported types for PayPal logo
     *
     * @return array
     */
    public function getAdditionalOptionsLogoTypes()
    {
        return [
            'wePrefer_150x60' => __('We prefer PayPal (150 X 60)'),
            'wePrefer_150x40' => __('We prefer PayPal (150 X 40)'),
            'nowAccepting_150x60' => __('Now accepting PayPal (150 X 60)'),
            'nowAccepting_150x40' => __('Now accepting PayPal (150 X 40)'),
            'paymentsBy_150x60' => __('Payments by PayPal (150 X 60)'),
            'paymentsBy_150x40' => __('Payments by PayPal (150 X 40)'),
            'shopNowUsing_150x60' => __('Shop now using (150 X 60)'),
            'shopNowUsing_150x40' => __('Shop now using (150 X 40)')
        ];
    }

    /**
     * Return PayPal logo URL with additional options
     *
     * @param string $localeCode Supported locale code
     * @param bool|string $type One of supported logo types
     * @return string|bool Logo Image URL or false if logo disabled in configuration
     */
    public function getAdditionalOptionsLogoUrl($localeCode, $type = false)
    {
        $configType = $this->_scopeConfig->getValue(
            $this->_mapGenericStyleFieldset('logo'),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
        if (!$configType) {
            return false;
        }
        $type = $type ? $type : $configType;
        $locale = $this->_getSupportedLocaleCode($localeCode);
        $supportedTypes = array_keys($this->getAdditionalOptionsLogoTypes());
        if (!in_array($type, $supportedTypes)) {
            $type = self::DEFAULT_LOGO_TYPE;
        }
        return sprintf('https://www.paypalobjects.com/%s/i/bnr/bnr_%s.gif', $locale, $type);
    }

    /**
     * Express Checkout button "flavors" source getter
     *
     * @return array
     */
    public function getExpressCheckoutButtonFlavors()
    {
        return [self::EC_FLAVOR_DYNAMIC => __('Dynamic'), self::EC_FLAVOR_STATIC => __('Static')];
    }

    /**
     * Express Checkout button types source getter
     *
     * @return array
     */
    public function getExpressCheckoutButtonTypes()
    {
        return [
            self::EC_BUTTON_TYPE_SHORTCUT => __('Shortcut'),
            self::EC_BUTTON_TYPE_MARK => __('Acceptance Mark Image')
        ];
    }

    /**
     * Payment actions source getter
     *
     * @return array
     */
    public function getPaymentActions()
    {
        $paymentActions = [
            self::PAYMENT_ACTION_AUTH => __('Authorization'),
            self::PAYMENT_ACTION_SALE => __('Sale'),
        ];
        if ($this->_methodCode !== null && $this->_methodCode == self::METHOD_WPP_EXPRESS) {
            $paymentActions[self::PAYMENT_ACTION_ORDER] = __('Order');
        }
        return $paymentActions;
    }

    /**
     * Require Billing Address source getter
     *
     * @return array
     */
    public function getRequireBillingAddressOptions()
    {
        return [
            self::REQUIRE_BILLING_ADDRESS_ALL => __('Yes'),
            self::REQUIRE_BILLING_ADDRESS_NO => __('No'),
            self::REQUIRE_BILLING_ADDRESS_VIRTUAL => __('For Virtual Quotes Only')
        ];
    }

    /**
     * Mapper from PayPal-specific payment actions to Magento payment actions
     *
     * @return string|null
     */
    public function getPaymentAction()
    {
        switch ($this->getValue('paymentAction')) {
            case self::PAYMENT_ACTION_AUTH:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE;
            case self::PAYMENT_ACTION_SALE:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_AUTHORIZE_CAPTURE;
            case self::PAYMENT_ACTION_ORDER:
                return \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER;
        }
        return null;
    }

    /**
     * Express Checkout "solution types" source getter
     * "sole" = "Express Checkout for Auctions" - PayPal allows guest checkout
     * "mark" = "Normal Express Checkout" - PayPal requires to checkout with PayPal buyer account only
     *
     * @return array
     */
    public function getExpressCheckoutSolutionTypes()
    {
        return [self::EC_SOLUTION_TYPE_SOLE => __('Yes'), self::EC_SOLUTION_TYPE_MARK => __('No')];
    }

    /**
     * Retrieve express checkout billing agreement signup options
     *
     * @return array
     */
    public function getExpressCheckoutBASignupOptions()
    {
        return [
            self::EC_BA_SIGNUP_AUTO => __('Auto'),
            self::EC_BA_SIGNUP_ASK => __('Ask Customer'),
            self::EC_BA_SIGNUP_NEVER => __('Never')
        ];
    }

    /**
     * Whether to ask customer to create billing agreements
     *
     * Unilateral payments are incompatible with the billing agreements
     *
     * @return bool
     */
    public function shouldAskToCreateBillingAgreement()
    {
        return $this->getValue('allow_ba_signup') === self::EC_BA_SIGNUP_ASK
            && !$this->shouldUseUnilateralPayments();
    }

    /**
     * Payment data delivery methods getter for PayPal Standard
     *
     * @return array
     */
    public function getWpsPaymentDeliveryMethods()
    {
        return [self::WPS_TRANSPORT_IPN => __('IPN (Instant Payment Notification) Only')];
    }

    /**
     * Return list of supported credit card types by Paypal Direct gateway
     *
     * @return array
     */
    public function getWppCcTypesAsOptionArray()
    {
        return $this->_cctypeFactory->create()->setAllowedTypes(
            ['AE', 'VI', 'MC', 'SM', 'SO', 'DI']
        )->toOptionArray();
    }

    /**
     * Return list of supported credit card types by Paypal Direct (Payflow Edition) gateway
     *
     * @return array
     */
    public function getWppPeCcTypesAsOptionArray()
    {
        return $this->_cctypeFactory->create()->setAllowedTypes(
            ['VI', 'MC', 'SM', 'SO', 'AE']
        )->toOptionArray();
    }

    /**
     * Return list of supported credit card types by Payflow Pro gateway
     *
     * @return array
     */
    public function getPayflowproCcTypesAsOptionArray()
    {
        return $this->_cctypeFactory->create()->setAllowedTypes(['AE', 'VI', 'MC', 'JCB', 'DI', 'DN'])->toOptionArray();
    }

    /**
     * Check whether the specified payment method is a CC-based one
     *
     * @param string $code
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public static function getIsCreditCardMethod($code)
    {
        switch ($code) {
            case self::METHOD_PAYFLOWPRO:
            case self::METHOD_PAYFLOWLINK:
            case self::METHOD_PAYFLOWADVANCED:
            case self::METHOD_HOSTEDPRO:
                return true;
        }
        return false;
    }

    /**
     * Check whether specified currency code is supported
     *
     * @param string $code
     * @return bool
     */
    public function isCurrencyCodeSupported($code)
    {
        if (in_array($code, $this->_supportedCurrencyCodes)) {
            return true;
        }
        if ($this->getMerchantCountry() == 'BR' && $code == 'BRL') {
            return true;
        }
        if ($this->getMerchantCountry() == 'MY' && $code == 'MYR') {
            return true;
        }
        if ($this->getMerchantCountry() == 'TR' && $code == 'TRY') {
            return true;
        }
        return false;
    }

    /**
     * Export page style current settings to specified object
     *
     * @param \Magento\Framework\DataObject $to
     * @return void
     */
    public function exportExpressCheckoutStyleSettings(\Magento\Framework\DataObject $to)
    {
        foreach ($this->_ecStyleConfigMap as $key => $exportKey) {
            $configValue = $this->getValue($key);
            if ($configValue) {
                $to->setData($exportKey, $configValue);
            }
        }
    }

    /**
     * Dynamic PayPal image URL getter
     *
     * Also can render dynamic Acceptance Mark
     *
     * @param string $type
     * @param string $localeCode
     * @param float $orderTotal
     * @param string $pal
     * @return string
     */
    protected function _getDynamicImageUrl($type, $localeCode, $orderTotal, $pal)
    {
        $params = [
            'cmd' => '_dynamic-image',
            'buttontype' => $type,
            'locale' => $this->_getSupportedLocaleCode($localeCode),
        ];
        if ($orderTotal) {
            $params['ordertotal'] = $this->formatPrice($orderTotal);
            if ($pal) {
                $params['pal'] = $pal;
            }
        }
        if ($params['locale'] == 'en_US') {
            switch ($type) {
                case self::EC_BUTTON_TYPE_MARK:
                    return 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppcredit-logo-medium.png';
                case self::EC_BUTTON_TYPE_SHORTCUT:
                default:
                    return 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-medium.png';
            }
        }
        return sprintf(
            'https://fpdbs%s.paypal.com/dynamicimageweb?%s',
            $this->getValue('sandboxFlag') ? '.sandbox' : '',
            http_build_query($params)
        );
    }

    /**
     * Check whether specified locale code is supported. Fallback to en_US
     *
     * @param string|null $localeCode
     * @return string
     */
    protected function _getSupportedLocaleCode($localeCode = null)
    {
        if (!$localeCode || !in_array($localeCode, $this->_supportedImageLocales)) {
            return 'en_US';
        }
        return $localeCode;
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getSpecificConfigPath($fieldName)
    {
        $path = null;
        switch ($this->_methodCode) {
            case self::METHOD_WPP_BML:
                $path = $this->_mapBmlFieldset($fieldName);
                break;
            case self::METHOD_WPP_PE_BML:
                $path = $this->_mapBmlPayflowFieldset($fieldName);
                break;
            case self::METHOD_WPP_EXPRESS:
            case self::METHOD_WPP_PE_EXPRESS:
            case self::METHOD_EXPRESS:
                $path = $this->_mapExpressFieldset($fieldName);
                break;
            case self::METHOD_BILLING_AGREEMENT:
            case self::METHOD_HOSTEDPRO:
                $path = $this->_mapMethodFieldset($fieldName);
                break;
        }

        if ($path === null) {
            switch ($this->_methodCode) {
                case self::METHOD_WPP_EXPRESS:
                case self::METHOD_WPP_BML:
                case self::METHOD_BILLING_AGREEMENT:
                case self::METHOD_HOSTEDPRO:
                    $path = $this->_mapWppFieldset($fieldName);
                    break;
                case self::METHOD_WPP_PE_EXPRESS:
                case self::METHOD_PAYFLOWADVANCED:
                case self::METHOD_PAYFLOWLINK:
                    $path = $this->_mapWpukFieldset($fieldName);
                    break;
            }
        }

        if ($path === null) {
            $path = $this->_mapGeneralFieldset($fieldName);
        }
        if ($path === null) {
            $path = $this->_mapGenericStyleFieldset($fieldName);
        }
        return $path;
    }

    /**
     * Map PayPal Express config fields
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mapExpressFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'transfer_shipping_options':
            case 'solution_type':
            case 'visible_on_cart':
            case 'visible_on_product':
            case 'require_billing_address':
            case 'authorization_honor_period':
            case 'order_valid_period':
            case 'child_authorization_number':
            case 'allow_ba_signup':
            case 'in_context':
            case 'merchant_id':
            case 'supported_locales':
                return "payment/{$this->_methodCode}/{$fieldName}";
            default:
                return $this->_mapMethodFieldset($fieldName);
        }
    }

    /**
     * Map PayPal Express Bill Me Later config fields
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapBmlFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'allow_ba_signup':
                return "payment/" . self::METHOD_WPP_EXPRESS . "/{$fieldName}";
            default:
                return $this->_mapExpressFieldset($fieldName);
        }
    }

    /**
     * Map PayPal Express Bill Me Later config fields (Payflow Edition)
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapBmlPayflowFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'allow_ba_signup':
                return "payment/" . self::METHOD_WPP_PE_EXPRESS . "/{$fieldName}";
            default:
                return $this->_mapExpressFieldset($fieldName);
        }
    }

    /**
     * Map PayPal Direct config fields
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapDirectFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'useccv':
                return "payment/{$this->_methodCode}/{$fieldName}";
            default:
                return $this->_mapMethodFieldset($fieldName);
        }
    }

    /**
     * Map PayPal Website Payments Pro common config fields
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mapWppFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'api_authentication':
            case 'api_username':
            case 'api_password':
            case 'api_signature':
            case 'api_cert':
            case 'sandbox_flag':
            case 'use_proxy':
            case 'proxy_host':
            case 'proxy_port':
            case 'button_flavor':
            case 'button_type':
                return "paypal/wpp/{$fieldName}";
            default:
                return null;
        }
    }

    /**
     * Map PayPal Website Payments Pro common config fields
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mapWpukFieldset($fieldName)
    {
        $pathPrefix = 'paypal/wpuk';
        if ($this->_methodCode == self::METHOD_WPP_PE_EXPRESS && $this->isMethodAvailable(self::METHOD_PAYFLOWLINK)) {
            $pathPrefix = 'payment/payflow_link';
        } elseif ($this->_methodCode == self::METHOD_WPP_PE_EXPRESS && $this->isMethodAvailable(
            self::METHOD_PAYFLOWADVANCED
        )
        ) {
            $pathPrefix = 'payment/payflow_advanced';
        } elseif ($this->_methodCode == self::METHOD_WPP_PE_EXPRESS) {
            $pathPrefix = 'payment/payflowpro';
        } elseif ($this->_methodCode == self::METHOD_PAYFLOWADVANCED || $this->_methodCode == self::METHOD_PAYFLOWLINK
        ) {
            return 'payment/' . $this->_methodCode . '/' . $fieldName;
        }
        switch ($fieldName) {
            case 'partner':
            case 'user':
            case 'vendor':
            case 'pwd':
            case 'sandbox_flag':
            case 'use_proxy':
            case 'proxy_host':
            case 'proxy_port':
                return $pathPrefix . '/' . $fieldName;
            default:
                return null;
        }
    }

    /**
     * Map PayPal common style config fields
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mapGenericStyleFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'logo':
            case 'page_style':
            case 'paypal_hdrimg':
            case 'paypal_hdrbackcolor':
            case 'paypal_hdrbordercolor':
            case 'paypal_payflowcolor':
            case 'disable_funding_options':
                return "paypal/style/{$fieldName}";
            default:
                return $this->mapButtonStyles($fieldName);
        }
    }

    /**
     * Map PayPal General Settings
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _mapGeneralFieldset($fieldName)
    {
        switch ($fieldName) {
            case 'business_account':
            case 'merchant_country':
                return "paypal/general/{$fieldName}";
            default:
                return null;
        }
    }

    /**
     * Map PayPal General Settings
     *
     * @param string $fieldName
     * @return string|null
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _mapMethodFieldset($fieldName)
    {
        if (!$this->_methodCode) {
            return null;
        }
        switch ($fieldName) {
            case 'active':
            case 'title':
            case 'payment_action':
            case 'allowspecific':
            case 'specificcountry':
            case 'line_items_enabled':
            case 'cctypes':
            case 'sort_order':
            case 'debug':
            case 'verify_peer':
                return "payment/{$this->_methodCode}/{$fieldName}";
            default:
                return null;
        }
    }

    /**
     * Map PayPal button style config fields
     *
     * @param string $fieldName
     * @return null|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function mapButtonStyles(string $fieldName)
    {
        $page = substr($fieldName, 0, (int)strpos($fieldName, '_page_button_'));

        if (!$page) {
            return null;
        }

        switch ($fieldName) {
            case "{$page}_page_button_customize":
            case "{$page}_page_button_layout":
            case "{$page}_page_button_size":
            case "{$page}_page_button_color":
            case "{$page}_page_button_shape":
            case "{$page}_page_button_label":
            case "{$page}_page_button_mx_installment_period":
            case "{$page}_page_button_br_installment_period":
                return "paypal/style/{$fieldName}";
            default:
                return null;
        }
    }

    /**
     * Payment API authentication methods source getter
     *
     * @return array
     */
    public function getApiAuthenticationMethods()
    {
        return ['0' => __('API Signature'), '1' => __('API Certificate')];
    }

    /**
     * Api certificate getter
     *
     * @return string
     */
    public function getApiCertificate()
    {
        $websiteId = $this->_storeManager->getStore($this->_storeId)->getWebsiteId();
        return $this->_certFactory->create()->loadByWebsite($websiteId, false)->getCertPath();
    }

    /**
     * Get PublisherId from stored config
     *
     * @return mixed
     */
    public function getBmlPublisherId()
    {
        return $this->_scopeConfig->getValue(
            'payment/' . self::METHOD_WPP_BML . '/publisher_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Get Display option from stored config
     *
     * @param string $section
     *
     * @return mixed
     */
    public function getBmlDisplay($section)
    {
        $display = $this->_scopeConfig->getValue(
            'payment/' . self::METHOD_WPP_BML . '/' . $section . '_display',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
        $bmlActive = $this->_scopeConfig->getValue(
            'payment/' . self::METHOD_WPP_BML . '/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
        $bmlUkActive = $this->_scopeConfig->getValue(
            'payment/' . self::METHOD_WPP_PE_BML . '/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
        return (($bmlActive && $this->isMethodActive(self::METHOD_WPP_EXPRESS))
            || ($bmlUkActive && $this->isMethodActive(self::METHOD_WPP_PE_EXPRESS))) ? $display : 0;
    }

    /**
     * Get Position option from stored config
     *
     * @param string $section
     *
     * @return mixed
     */
    public function getBmlPosition($section)
    {
        return $this->_scopeConfig->getValue(
            'payment/' . self::METHOD_WPP_BML . '/' . $section . '_position',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * Get Size option from stored config
     *
     * @param string $section
     *
     * @return mixed
     */
    public function getBmlSize($section)
    {
        return $this->_scopeConfig->getValue(
            'payment/' . self::METHOD_WPP_BML . '/' . $section . '_size',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }
}
