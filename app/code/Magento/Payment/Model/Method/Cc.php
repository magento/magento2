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
 * @package     Magento_Payment
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Payment\Model\Method;

class Cc extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_formBlockType = 'Magento\Payment\Block\Form\Cc';
    protected $_infoBlockType = 'Magento\Payment\Block\Info\Cc';
    protected $_canSaveCc     = false;

    /**
     * @var \Magento\App\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * Locale model
     *
     * @var \Magento\Core\Model\LocaleInterface
     */
    protected $_locale;

    /**
     * Centinel service model
     *
     * @var \Magento\Centinel\Model\Service
     */
    protected $_centinelService;

    /**
     * Construct
     *
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * Construct
     *
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\App\ModuleListInterface $moduleList
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Core\Model\Log\AdapterFactory $logAdapterFactory
     * @param \Magento\Core\Model\LocaleInterface $locale
     * @param \Magento\Centinel\Model\Service $centinelService
     * @param array $data
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
        array $data = array()
    ) {
        parent::__construct($eventManager, $paymentData, $coreStoreConfig, $logAdapterFactory, $data);
        $this->_moduleList = $moduleList;
        $this->_logger = $logger;
        $this->_locale = $locale;
        $this->_centinelService = $centinelService;
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  \Magento\Payment\Model\Info
     */
    public function assignData($data)
    {
        if (!($data instanceof \Magento\Object)) {
            $data = new \Magento\Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear())
            ;
        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return \Magento\Payment\Model\Method\Cc
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc) {
            $info->setCcNumberEnc($info->encrypt($info->getCcNumber()));
        }
        //$info->setCcCidEnc($info->encrypt($info->getCcCid()));
        $info->setCcNumber(null)
            ->setCcCid(null);
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return \Magento\Payment\Model\Method\Cc
     * @throws \Magento\Core\Exception
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
            if ($this->validateCcNum($ccNumber)
                // Other credit card type number validation
                || ($this->otherCcType($info->getCcType()) && $this->validateCcNumOther($ccNumber))) {

                $ccTypeRegExpList = array(
                    //Solo, Switch or Maestro. International safe
                    'SO' => '/(^(6334)[5-9](\d{11}$|\d{13,14}$))|(^(6767)(\d{12}$|\d{14,15}$))/',
                    'SM' => '/(^(5[0678])\d{11,18}$)|(^(6[^05])\d{11,18}$)|(^(601)[^1]\d{9,16}$)|(^(6011)\d{9,11}$)'
                            . '|(^(6011)\d{13,16}$)|(^(65)\d{11,13}$)|(^(65)\d{15,18}$)'
                            . '|(^(49030)[2-9](\d{10}$|\d{12,13}$))|(^(49033)[5-9](\d{10}$|\d{12,13}$))'
                            . '|(^(49110)[1-2](\d{10}$|\d{12,13}$))|(^(49117)[4-9](\d{10}$|\d{12,13}$))'
                            . '|(^(49118)[0-2](\d{10}$|\d{12,13}$))|(^(4936)(\d{12}$|\d{14,15}$))/',
                    // Visa
                    'VI'  => '/^4[0-9]{12}([0-9]{3})?$/',
                    // Master Card
                    'MC'  => '/^5[1-5][0-9]{14}$/',
                    // American Express
                    'AE'  => '/^3[47][0-9]{13}$/',
                    // Discover
                    'DI'  => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})'
                        . '|36[0-9]{12}|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}'
                        . '|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}'
                        . '|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}'
                        . '|5[0-9]{14}))$/',
                    // JCB
                    'JCB' => '/^(30[0-5][0-9]{13}|3095[0-9]{12}|35(2[8-9][0-9]{12}|[3-8][0-9]{13})|36[0-9]{12}'
                        . '|3[8-9][0-9]{14}|6011(0[0-9]{11}|[2-4][0-9]{11}|74[0-9]{10}|7[7-9][0-9]{10}'
                        . '|8[6-9][0-9]{10}|9[0-9]{11})|62(2(12[6-9][0-9]{10}|1[3-9][0-9]{11}|[2-8][0-9]{12}'
                        . '|9[0-1][0-9]{11}|92[0-5][0-9]{10})|[4-6][0-9]{13}|8[2-8][0-9]{12})|6(4[4-9][0-9]{13}'
                        . '|5[0-9]{14}))$/'
                );

                $ccNumAndTypeMatches = isset($ccTypeRegExpList[$info->getCcType()])
                    && preg_match($ccTypeRegExpList[$info->getCcType()], $ccNumber);
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
            throw new \Magento\Core\Exception($errorMsg);
        }

        //This must be after all validation conditions
        if ($this->getIsCentinelValidationEnabled()) {
            $this->getCentinelValidator()->validate($this->getCentinelValidationData());
        }

        return $this;
    }

    public function hasVerification()
    {
        $configData = $this->getConfigData('useccv');
        if (is_null($configData)) {
            return true;
        }
        return (bool) $configData;
    }

    public function getVerificationRegEx()
    {
        $verificationExpList = array(
            'VI' => '/^[0-9]{3}$/', // Visa
            'MC' => '/^[0-9]{3}$/',       // Master Card
            'AE' => '/^[0-9]{4}$/',        // American Express
            'DI' => '/^[0-9]{3}$/',          // Discovery
            'SS' => '/^[0-9]{3,4}$/',
            'SM' => '/^[0-9]{3,4}$/', // Switch or Maestro
            'SO' => '/^[0-9]{3,4}$/', // Solo
            'OT' => '/^[0-9]{3,4}$/',
            'JCB' => '/^[0-9]{3,4}$/' //JCB
        );
        return $verificationExpList;
    }

    protected function _validateExpDate($expYear, $expMonth)
    {
        $date = $this->_locale->date();
        if (!$expYear || !$expMonth || ($date->compareYear($expYear) == 1)
            || ($date->compareYear($expYear) == 0 && ($date->compareMonth($expMonth) == 1))
        ) {
            return false;
        }
        return true;
    }

    public function otherCcType($type)
    {
        return $type=='OT';
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

        for ($i=0; $i<strlen($cardNumber); $i++) {
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
        return ($numSum % 10 == 0);
    }

    /**
     * Other credit cart type number validation
     *
     * @param string $ccNumber
     * @return boolean
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
        return $this->getConfigData('cctypes', ($quote ? $quote->getStoreId() : null))
            && parent::isAvailable($quote);
    }

    /**
     * Whether centinel service is enabled
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCentinelValidationEnabled()
    {
        return null !== $this->_moduleList->getModule('Magento_Centinel') && 1 == $this->getConfigData('centinel');
    }

    /**
     * Instantiate centinel validator model
     *
     * @return \Magento\Centinel\Model\Service
     */
    public function getCentinelValidator()
    {
        $this->_centinelService
            ->setIsModeStrict($this->getConfigData('centinel_is_mode_strict'))
            ->setCustomApiEndpointUrl($this->getConfigData('centinel_api_url'))
            ->setStore($this->getStore())
            ->setIsPlaceOrder($this->_isPlaceOrder());
        return $this->_centinelService;
    }

    /**
     * Return data for Centinel validation
     *
     * @return \Magento\Object
     */
    public function getCentinelValidationData()
    {
        $info = $this->getInfoInstance();
        $params = new \Magento\Object();
        $params
            ->setPaymentMethodCode($this->getCode())
            ->setCardType($info->getCcType())
            ->setCardNumber($info->getCcNumber())
            ->setCardExpMonth($info->getCcExpMonth())
            ->setCardExpYear($info->getCcExpYear())
            ->setAmount($this->_getAmount())
            ->setCurrencyCode($this->_getCurrencyCode())
            ->setOrderNumber($this->_getOrderId());
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
