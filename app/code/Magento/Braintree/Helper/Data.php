<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Payment\Model\Config as PaymentConfig;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH_KOUNT_ID = 'payment/braintree/kount_id';

    /**
     * @var int|null
     */
    protected $today = null;

    /**
     * @var PaymentConfig
     */
    protected $paymentConfig;

    /**
     * @var \Magento\Braintree\Model\Config\Cc
     */
    protected $braintreeCcConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateFormat;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var cached cc types
     */
    protected $ccTypes = null;

    /**
     * @param Context $context
     * @param PaymentConfig $paymentConfig
     * @param \Magento\Braintree\Model\Config\Cc $braintreeCcConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateFormat
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        Context $context,
        PaymentConfig $paymentConfig,
        \Magento\Braintree\Model\Config\Cc $braintreeCcConfig,
        \Magento\Framework\Stdlib\DateTime $dateFormat,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->paymentConfig = $paymentConfig;
        $this->braintreeCcConfig = $braintreeCcConfig;
        $this->dateFormat = $dateFormat;
        $this->dateTime = $dateTime;
    }

    /**
     * Finds credit card type by type name using global payments config
     *
     * @param string $name
     * @return string|bool
     */
    public function getCcTypeCodeByName($name)
    {
        if (!$this->ccTypes) {
            $ccTypes = $this->paymentConfig->getCcTypes();
            $this->ccTypes = array_flip($ccTypes);
        }
        if (isset($this->ccTypes[$name])) {
            return $this->ccTypes[$name];
        } else {
            return false;
        }
    }

    /**
     * Finds credit card type by type name using global payments config
     *
     * @param string $code
     * @return mixed
     */
    public function getCcTypeNameByCode($code)
    {
        $ccTypes = $this->paymentConfig->getCcTypes();
        if (isset($ccTypes[$code])) {
            return $ccTypes[$code];
        } else {
            return false;
        }
    }

    /**
     * Finds all credit card types using global payments config
     *
     * @return mixed
     */
    public function getCcTypes()
    {
        $ccTypes = $this->paymentConfig->getCcTypes();
        if (is_array($ccTypes)) {
            return $ccTypes;
        } else {
            return false;
        }
    }

    /**
     * Get the configured Kount ID
     *
     * @return mixed
     */
    public function getKountId()
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_KOUNT_ID);
    }

    /**
     * Removes Magento added transaction id suffix if applicable
     *
     * @param string $transactionId
     * @return string
     */
    public function clearTransactionId($transactionId)
    {
        $suffixes = [
            '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE,
            '-' . \Magento\Sales\Model\Order\Payment\Transaction::TYPE_VOID,
        ];
        foreach ($suffixes as $suffix) {
            if (strpos($transactionId, $suffix) !== false) {
                $transactionId = str_replace($suffix, '', $transactionId);
            }
        }
        return $transactionId;
    }

    /**
     * Returns today year
     *
     * @return string
     */
    public function getTodayYear()
    {
        if (!$this->today) {
            $this->today = $this->dateTime->gmtTimestamp();
        }
        return date('Y', $this->today);
    }

    /**
     * Returns today month
     *
     * @return string
     */
    public function getTodayMonth()
    {
        if (!$this->today) {
            $this->today = $this->dateTime->gmtTimestamp();
        }
        return date('m', $this->today);
    }

    /**
     * Generates md5 hash to be used as customer id
     *
     * @param string $customerId
     * @param string $email
     * @return string
     */
    public function generateCustomerId($customerId, $email)
    {
        return md5($customerId . '-' . $email);
    }

    /**
     * Retrieve available credit card types as associative array code & title
     *
     * @param string $country
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getCcAvailableCardTypes($country = null)
    {
        $types = array_flip(explode(',', $this->braintreeCcConfig->getConfigData('cctypes')));
        $mergedArray= [];

        if (is_array($types)) {
            foreach (array_keys($types) as $type) {
                $types[$type] = $this->getCcTypeNameByCode($type);
            }
        }
        //merge options then filter by a specific country
        $countrySpecificTypes = $this->braintreeCcConfig->getCountrySpecificCardTypeConfig();


        //include all country specific in types
        if (is_array($countrySpecificTypes)) {
            foreach ($countrySpecificTypes as $countryArray) {
                foreach ($countryArray as $ccType) {
                    $types[$ccType]=$this->getCcTypeNameByCode($ccType);
                }
            }
        }

        //preserve the same credit card order
        $allTypes = $this->getCcTypes();
        if (is_array($allTypes)) {
            foreach ($allTypes as $ccTypeCode => $ccTypeName) {
                if (array_key_exists($ccTypeCode, $types)) {
                    $mergedArray[$ccTypeCode] = $ccTypeName;
                }
            }
        }

        if ($country) {
            $countrySpecificTypesApplicable = $this->braintreeCcConfig->getApplicableCardTypes($country);
            if (!empty($countrySpecificTypesApplicable)) {
                foreach (array_keys($mergedArray) as $code) {
                    if (!in_array($code, $countrySpecificTypesApplicable)) {
                        unset($mergedArray[$code]);
                    }
                }
            }
        }

        return $mergedArray;
    }
}
