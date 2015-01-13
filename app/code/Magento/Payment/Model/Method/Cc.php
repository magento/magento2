<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model\Method;

class Cc extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var string
     */
    protected $_formBlockType = 'Magento\Payment\Block\Form\Cc';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Cc';

    /**
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Centinel service model
     *
     * @var \Magento\Centinel\Model\Service
     */
    protected $_centinelService;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Centinel\Model\Service $centinelService
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Centinel\Model\Service $centinelService,
        array $data = []
    ) {
        parent::__construct($eventManager, $paymentData, $scopeConfig, $logger, $data);
        $this->_moduleList = $moduleList;
        $this->_localeDate = $localeDate;
        $this->_centinelService = $centinelService;
    }

    /**
     * Assign data to info model instance
     *
     * @param \Magento\Framework\Object|mixed $data
     * @return $this
     */
    public function assignData($data)
    {
        if (!$data instanceof \Magento\Framework\Object) {
            $data = new \Magento\Framework\Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType(
            $data->getCcType()
        )->setCcOwner(
            $data->getCcOwner()
        )->setCcLast4(
            substr($data->getCcNumber(), -4)
        )->setCcNumber(
            $data->getCcNumber()
        )->setCcCid(
            $data->getCcCid()
        )->setCcExpMonth(
            $data->getCcExpMonth()
        )->setCcExpYear(
            $data->getCcExpYear()
        )->setCcSsIssue(
            $data->getCcSsIssue()
        )->setCcSsStartMonth(
            $data->getCcSsStartMonth()
        )->setCcSsStartYear(
            $data->getCcSsStartYear()
        );
        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return $this
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc) {
            $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        }
        //$info->setCcCidEnc($info->encrypt($info->getCcCid()));
        $info->setCcNumber(null)->setCcCid(null);
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate()
    {
        /*
         * calling parent validate function
         */
        parent::validate();

        $info = $this->getInfoInstance();
        $errorMsg = false;
        $availableTypes = explode(',', $this->getConfigData('cctypes'));

        $ccNumber = $info->getCcNumber();

        // remove credit card number delimiters such as "-" and space
        $ccNumber = preg_replace('/[\-\s]+/', '', $ccNumber);
        $info->setCcNumber($ccNumber);

        $ccType = '';

        if (in_array($info->getCcType(), $availableTypes)) {
            if ($this->validateCcNum(
                $ccNumber
            ) || $this->otherCcType(
                $info->getCcType()
            ) && $this->validateCcNumOther(
                // Other credit card type number validation
                $ccNumber
            )
            ) {
                $ccTypeRegExpList = [
                    //Solo, Switch or Maestro. International safe
                    'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/',
                    'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)' .
                    '|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)' .
                    '|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))' .
                    '|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))' .
                    '|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
                    // Visa
                    'VI' => '/^4[0-9]{12}([0-9]{3})?$/',
                    // Master Card
                    'MC' => '/^5[1-5][0-9]{14}$/',
                    // American Express
                    'AE' => '/^3[47][0-9]{13}$/',
                    // Discover
                    'DI' => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})' .
                    '|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}' .
                    '|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}' .
                    '|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}' .
                    '|5[0-9]{14}))$/',
                    // JCB
                    'JCB' => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}' .
                    '|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}' .
                    '|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}' .
                    '|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}' .
                    '|5[0-9]{14}))$/',
                ];

                $ccNumAndTypeMatches = isset(
                    $ccTypeRegExpList[$info->getCcType()]
                ) && preg_match(
                    $ccTypeRegExpList[$info->getCcType()],
                    $ccNumber
                );
                $ccType = $ccNumAndTypeMatches ? $info->getCcType() : 'OT';

                if (!$ccNumAndTypeMatches && !$this->otherCcType($info->getCcType())) {
                    $errorMsg = __('Credit card number mismatch with credit card type.');
                }
            } else {
                $errorMsg = __('Invalid Credit Card Number');
            }
        } else {
            $errorMsg = __('Credit card type is not allowed for this payment method.');
        }

        //validate credit card verification number
        if ($errorMsg === false && $this->hasVerification()) {
            $verifcationRegEx = $this->getVerificationRegEx();
            $regExp = isset($verifcationRegEx[$info->getCcType()]) ? $verifcationRegEx[$info->getCcType()] : '';
            if (!$info->getCcCid() || !$regExp || !preg_match($regExp, $info->getCcCid())) {
                $errorMsg = __('Please enter a valid credit card verification number.');
            }
        }

        if ($ccType != 'SS' && !$this->_validateExpDate($info->getCcExpYear(), $info->getCcExpMonth())) {
            $errorMsg = __('We found an incorrect credit card expiration date.');
        }

        if ($errorMsg) {
            throw new \Magento\Framework\Model\Exception($errorMsg);
        }

        //This must be after all validation conditions
        if ($this->getIsCentinelValidationEnabled()) {
            $this->getCentinelValidator()->validate($this->getCentinelValidationData());
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasVerification()
    {
        $configData = $this->getConfigData('useccv');
        if (is_null($configData)) {
            return true;
        }
        return (bool)$configData;
    }

    /**
     * @return array
     */
    public function getVerificationRegEx()
    {
        $verificationExpList = [
            'VI' => '/^[0-9]{3}$/',
            'MC' => '/^[0-9]{3}$/',
            'AE' => '/^[0-9]{4}$/',
            'DI' => '/^[0-9]{3}$/',
            'SS' => '/^[0-9]{3,4}$/',
            'SM' => '/^[0-9]{3,4}$/',
            'SO' => '/^[0-9]{3,4}$/',
            'OT' => '/^[0-9]{3,4}$/',
            'JCB' => '/^[0-9]{3,4}$/',
        ];
        return $verificationExpList;
    }

    /**
     * @param string $expYear
     * @param string $expMonth
     * @return bool
     */
    protected function _validateExpDate($expYear, $expMonth)
    {
        $date = $this->_localeDate->date();
        if (!$expYear || !$expMonth || $date->compareYear(
            $expYear
        ) == 1 || $date->compareYear(
            $expYear
        ) == 0 && $date->compareMonth(
            $expMonth
        ) == 1
        ) {
            return false;
        }
        return true;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function otherCcType($type)
    {
        return $type == 'OT';
    }

    /**
     * Validate credit card number
     *
     * @param   string $ccNumber
     * @return  bool
     */
    public function validateCcNum($ccNumber)
    {
        $cardNumber = strrev($ccNumber);
        $numSum = 0;

        for ($i = 0; $i < strlen($cardNumber); $i++) {
            $currentNum = substr($cardNumber, $i, 1);

            /**
             * Double every second digit
             */
            if ($i % 2 == 1) {
                $currentNum *= 2;
            }

            /**
             * Add digits of 2-digit numbers together
             */
            if ($currentNum > 9) {
                $firstNum = $currentNum % 10;
                $secondNum = ($currentNum - $firstNum) / 10;
                $currentNum = $firstNum + $secondNum;
            }

            $numSum += $currentNum;
        }

        /**
         * If the total has no remainder it's OK
         */
        return $numSum % 10 == 0;
    }

    /**
     * Other credit cart type number validation
     *
     * @param string $ccNumber
     * @return bool
     */
    public function validateCcNumOther($ccNumber)
    {
        return preg_match('/^\\d+$/', $ccNumber);
    }

    /**
     * Check whether there are CC types set in configuration
     *
     * @param \Magento\Sales\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return $this->getConfigData('cctypes', $quote ? $quote->getStoreId() : null) && parent::isAvailable($quote);
    }

    /**
     * Whether centinel service is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCentinelValidationEnabled()
    {
        return $this->_moduleList->has('Magento_Centinel') && 1 == $this->getConfigData('centinel');
    }

    /**
     * Instantiate centinel validator model
     *
     * @return \Magento\Centinel\Model\Service
     */
    public function getCentinelValidator()
    {
        $this->_centinelService->setIsModeStrict(
            $this->getConfigData('centinel_is_mode_strict')
        )->setCustomApiEndpointUrl(
            $this->getConfigData('centinel_api_url')
        )->setStore(
            $this->getStore()
        )->setIsPlaceOrder(
            $this->_isPlaceOrder()
        );
        return $this->_centinelService;
    }

    /**
     * Return data for Centinel validation
     *
     * @return \Magento\Framework\Object
     */
    public function getCentinelValidationData()
    {
        $info = $this->getInfoInstance();
        $params = new \Magento\Framework\Object();
        $params->setPaymentMethodCode(
            $this->getCode()
        )->setCardType(
            $info->getCcType()
        )->setCardNumber(
            $info->getCcNumber()
        )->setCardExpMonth(
            $info->getCcExpMonth()
        )->setCardExpYear(
            $info->getCcExpYear()
        )->setAmount(
            $this->_getAmount()
        )->setCurrencyCode(
            $this->_getCurrencyCode()
        )->setOrderNumber(
            $this->_getOrderId()
        );
        return $params;
    }

    /**
     * Order increment ID getter (either real from order or a reserved from quote)
     *
     * @return string
     */
    private function _getOrderId()
    {
        $info = $this->getInfoInstance();

        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getIncrementId();
        } else {
            if (!$info->getQuote()->getReservedOrderId()) {
                $info->getQuote()->reserveOrderId();
            }
            return $info->getQuote()->getReservedOrderId();
        }
    }

    /**
     * Grand total getter
     *
     * @return string
     */
    private function _getAmount()
    {
        $info = $this->getInfoInstance();
        if ($this->_isPlaceOrder()) {
            return (double)$info->getOrder()->getQuoteBaseGrandTotal();
        } else {
            return (double)$info->getQuote()->getBaseGrandTotal();
        }
    }

    /**
     * Currency code getter
     *
     * @return string
     */
    private function _getCurrencyCode()
    {
        $info = $this->getInfoInstance();

        if ($this->_isPlaceOrder()) {
            return $info->getOrder()->getBaseCurrencyCode();
        } else {
            return $info->getQuote()->getBaseCurrencyCode();
        }
    }

    /**
     * Whether current operation is order placement
     *
     * @return bool
     */
    private function _isPlaceOrder()
    {
        $info = $this->getInfoInstance();
        if ($info instanceof \Magento\Sales\Model\Quote\Payment) {
            return false;
        } elseif ($info instanceof \Magento\Sales\Model\Order\Payment) {
            return true;
        }
    }
}
