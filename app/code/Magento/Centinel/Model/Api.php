<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Centinel\Model;

/**
 * 3D Secure Validation Library for Payment
 */
include_once 'CardinalCommerce/CentinelClient.php';
/**
 * 3D Secure Validation Api
 */
class Api extends \Magento\Framework\Object
{
    /**
     * Fields that should be replaced in debug with '***'
     *
     * @var string[]
     */
    protected $_debugReplacePrivateDataKeys = ['TransactionPwd', 'CardNumber', 'CardExpMonth', 'CardExpYear'];

    /**
     * Array of ISO 4217 Currency codes and numbers
     *
     * @var array
     */
    protected static $_iso4217Currencies = [
        'AED' => '784',
        'AFN' => '971',
        'ALL' => '008',
        'AMD' => '051',
        'ANG' => '532',
        'AOA' => '973',
        'ARS' => '032',
        'AUD' => '036',
        'AWG' => '533',
        'AZN' => '944',
        'BAM' => '977',
        'BBD' => '052',
        'BDT' => '050',
        'BGN' => '975',
        'BHD' => '048',
        'BIF' => '108',
        'BMD' => '060',
        'BND' => '096',
        'BOB' => '068',
        'BOV' => '984',
        'BRL' => '986',
        'BSD' => '044',
        'BTN' => '064',
        'BWP' => '072',
        'BYR' => '974',
        'BZD' => '084',
        'CAD' => '124',
        'CDF' => '976',
        'CHE' => '947',
        'CHF' => '756',
        'CHW' => '948',
        'CLF' => '990',
        'CLP' => '152',
        'CNY' => '156',
        'COP' => '170',
        'COU' => '970',
        'CRC' => '188',
        'CUC' => '931',
        'CUP' => '192',
        'CVE' => '132',
        'CZK' => '203',
        'DJF' => '262',
        'DKK' => '208',
        'DOP' => '214',
        'DZD' => '012',
        'EEK' => '233',
        'EGP' => '818',
        'ERN' => '232',
        'ETB' => '230',
        'EUR' => '978',
        'FJD' => '242',
        'FKP' => '238',
        'GBP' => '826',
        'GEL' => '981',
        'GHS' => '936',
        'GIP' => '292',
        'GMD' => '270',
        'GNF' => '324',
        'GTQ' => '320',
        'GYD' => '328',
        'HKD' => '344',
        'HNL' => '340',
        'HRK' => '191',
        'HTG' => '332',
        'HUF' => '348',
        'IDR' => '360',
        'ILS' => '376',
        'INR' => '356',
        'IQD' => '368',
        'IRR' => '364',
        'ISK' => '352',
        'JMD' => '388',
        'JOD' => '400',
        'JPY' => '392',
        'KES' => '404',
        'KGS' => '417',
        'KHR' => '116',
        'KMF' => '174',
        'KPW' => '408',
        'KRW' => '410',
        'KWD' => '414',
        'KYD' => '136',
        'KZT' => '398',
        'LAK' => '418',
        'LBP' => '422',
        'LKR' => '144',
        'LRD' => '430',
        'LSL' => '426',
        'LTL' => '440',
        'LVL' => '428',
        'LYD' => '434',
        'MAD' => '504',
        'MDL' => '498',
        'MGA' => '969',
        'MKD' => '807',
        'MMK' => '104',
        'MNT' => '496',
        'MOP' => '446',
        'MRO' => '478',
        'MUR' => '480',
        'MVR' => '462',
        'MWK' => '454',
        'MXN' => '484',
        'MXV' => '979',
        'MYR' => '458',
        'MZN' => '943',
        'NAD' => '516',
        'NGN' => '566',
        'NIO' => '558',
        'NOK' => '578',
        'NPR' => '524',
        'NZD' => '554',
        'OMR' => '512',
        'PAB' => '590',
        'PEN' => '604',
        'PGK' => '598',
        'PHP' => '608',
        'PKR' => '586',
        'PLN' => '985',
        'PYG' => '600',
        'QAR' => '634',
        'RON' => '946',
        'RSD' => '941',
        'RUB' => '643',
        'RWF' => '646',
        'SAR' => '682',
        'SBD' => '090',
        'SCR' => '690',
        'SDG' => '938',
        'SEK' => '752',
        'SGD' => '702',
        'SHP' => '654',
        'SLL' => '694',
        'SOS' => '706',
        'SRD' => '968',
        'STD' => '678',
        'SYP' => '760',
        'SZL' => '748',
        'THB' => '764',
        'TJS' => '972',
        'TMT' => '934',
        'TND' => '788',
        'TOP' => '776',
        'TRY' => '949',
        'TTD' => '780',
        'TWD' => '901',
        'TZS' => '834',
        'UAH' => '980',
        'UGX' => '800',
        'USD' => '840',
        'USN' => '997',
        'USS' => '998',
        'UYU' => '858',
        'UZS' => '860',
        'VEF' => '937',
        'VND' => '704',
        'VUV' => '548',
        'WST' => '882',
        'XAF' => '950',
        'XAG' => '961',
        'XAU' => '959',
        'XBA' => '955',
        'XBB' => '956',
        'XBC' => '957',
        'XBD' => '958',
        'XCD' => '951',
        'XDR' => '960',
        'XOF' => '952',
        'XPD' => '964',
        'XPF' => '953',
        'XPT' => '962',
        'XTS' => '963',
        'XXX' => '999',
        'YER' => '886',
        'ZAR' => '710',
        'ZMK' => '894',
        'ZWL' => '932',
    ];

    /**
     * Centinel validation client
     *
     * @var \CentinelClient
     */
    protected $_clientInstance = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(\Psr\Log\LoggerInterface $logger, array $data = [])
    {
        $this->logger = $logger;
        parent::__construct($data);
    }

    /**
     * Return Centinel thin client object
     *
     * @return \CentinelClient
     */
    protected function _getClientInstance()
    {
        if (empty($this->_clientInstance)) {
            $this->_clientInstance = new \CentinelClient();
        }
        return $this->_clientInstance;
    }

    /**
     * Return Centinel Api version
     *
     * @return string
     */
    protected function _getVersion()
    {
        return '1.7';
    }

    /**
     * Return transaction type. according centinel documetation it should be "C"
     *
     * @return string
     */
    protected function _getTransactionType()
    {
        return 'C';
    }

    /**
     * Return Timeout Connect
     *
     * @return int
     */
    protected function _getTimeoutConnect()
    {
        return 100;
    }

    /**
     * Return Timeout Read
     *
     * @return int
     */
    protected function _getTimeoutRead()
    {
        return 100;
    }

    /**
     * Call centinel api methods by given method name and data
     *
     * @param string $method
     * @param array $data
     * @return \CentinelClient
     * @throws \Exception
     */
    protected function _call($method, $data)
    {
        $client = $this->_getClientInstance();
        $request = array_merge(
            [
                'MsgType' => $method,
                'Version' => $this->_getVersion(),
                'ProcessorId' => $this->getProcessorId(),
                'MerchantId' => $this->getMerchantId(),
                'TransactionPwd' => $this->getTransactionPwd(),
                'TransactionType' => $this->_getTransactionType(),
            ],
            $data
        );

        $debugData = ['request' => $request];

        try {
            foreach ($request as $key => $val) {
                $client->add($key, $val);
            }
            $client->sendHttp($this->_getApiEndpointUrl(), $this->_getTimeoutConnect(), $this->_getTimeoutRead());
        } catch (\Exception $e) {
            $debugData['response'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
            $this->_debug($debugData);
            throw $e;
        }

        $debugData['response'] = $client->response;
        $this->_debug($debugData);

        return $client;
    }

    /**
     * Getter for API call URL
     *
     * @return string
     * @throws \Exception
     */
    protected function _getApiEndpointUrl()
    {
        if ($this->getIsTestMode()) {
            return 'https://centineltest.cardinalcommerce.com/maps/txns.asp';
        }
        $url = $this->getApiEndpointUrl();
        if (!$url) {
            throw new \Exception('Centinel API endpoint URL is not configured properly.');
        }
        return $url;
    }

    /**
     * Call centinel api lookup method
     *
     * @param \Magento\Framework\Object $data
     * @return \Magento\Framework\Object
     */
    public function callLookup($data)
    {
        $result = new \Magento\Framework\Object();

        $month = strlen($data->getCardExpMonth()) == 1 ? '0' . $data->getCardExpMonth() : $data->getCardExpMonth();
        $currencyCode = $data->getCurrencyCode();
        $currencyNumber = isset(
            self::$_iso4217Currencies[$currencyCode]
        ) ? self::$_iso4217Currencies[$currencyCode] : '';
        if (!$currencyNumber) {
            return $result->setErrorNo(1)->setErrorDesc(__('Unsupported currency code: %1.', $currencyCode));
        }

        $clientResponse = $this->_call(
            'cmpi_lookup',
            [
                'Amount' => round($data->getAmount() * 100),
                'CurrencyCode' => $currencyNumber,
                'CardNumber' => $data->getCardNumber(),
                'CardExpMonth' => $month,
                'CardExpYear' => $data->getCardExpYear(),
                'OrderNumber' => $data->getOrderNumber()
            ]
        );

        $result->setErrorNo($clientResponse->getValue('ErrorNo'));
        $result->setErrorDesc($clientResponse->getValue('ErrorDesc'));
        $result->setTransactionId($clientResponse->getValue('TransactionId'));
        $result->setEnrolled($clientResponse->getValue('Enrolled'));
        $result->setAcsUrl($clientResponse->getValue('ACSUrl'));
        $result->setPayload($clientResponse->getValue('Payload'));
        $result->setEciFlag($clientResponse->getValue('EciFlag'));

        return $result;
    }

    /**
     * Call centinel api authentication method
     *
     * @param \Magento\Framework\Object $data
     * @return \Magento\Framework\Object
     */
    public function callAuthentication($data)
    {
        $result = new \Magento\Framework\Object();

        $clientResponse = $this->_call(
            'cmpi_authenticate',
            ['TransactionId' => $data->getTransactionId(), 'PAResPayload' => $data->getPaResPayload()]
        );

        $result->setErrorNo($clientResponse->getValue('ErrorNo'));
        $result->setErrorDesc($clientResponse->getValue('ErrorDesc'));
        $result->setPaResStatus($clientResponse->getValue('PAResStatus'));
        $result->setSignatureVerification($clientResponse->getValue('SignatureVerification'));
        $result->setCavv($clientResponse->getValue('Cavv'));
        $result->setEciFlag($clientResponse->getValue('EciFlag'));
        $result->setXid($clientResponse->getValue('Xid'));

        return $result;
    }

    /**
     * Log debug data to file
     *
     * @param array $debugData
     * @return void
     */
    protected function _debug($debugData)
    {
        if ($this->getDebugFlag()) {
            $this->logger->debug($debugData);
        }
    }
}
