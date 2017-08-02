<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Quote payment information
 *
 * @api
 * @method \Magento\Quote\Model\ResourceModel\Quote\Payment _getResource()
 * @method \Magento\Quote\Model\ResourceModel\Quote\Payment getResource()
 * @method int getQuoteId()
 * @method \Magento\Quote\Model\Quote\Payment setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Quote\Model\Quote\Payment setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Quote\Model\Quote\Payment setUpdatedAt(string $value)
 * @method string getCcNumberEnc()
 * @method \Magento\Quote\Model\Quote\Payment setCcNumberEnc(string $value)
 * @method string getCcLast4()
 * @method \Magento\Quote\Model\Quote\Payment setCcLast4(string $value)
 * @method string getCcCidEnc()
 * @method \Magento\Quote\Model\Quote\Payment setCcCidEnc(string $value)
 * @method string getCcSsOwner()
 * @method \Magento\Quote\Model\Quote\Payment setCcSsOwner(string $value)
 * @method int getCcSsStartMonth()
 * @method \Magento\Quote\Model\Quote\Payment setCcSsStartMonth(int $value)
 * @method int getCcSsStartYear()
 * @method \Magento\Quote\Model\Quote\Payment setCcSsStartYear(int $value)
 * @method string getCcSsIssue()
 * @method \Magento\Quote\Model\Quote\Payment setCcSsIssue(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Payment extends \Magento\Payment\Model\Info implements PaymentInterface
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventPrefix = 'sales_quote_payment';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_eventObject = 'payment';

    /**
     * Quote model object
     *
     * @var \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    protected $_quote;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     * @since 2.0.0
     */
    protected $methodSpecificationFactory;

    /**
     * @var array
     * @since 2.2.0
     */
    private $additionalChecks;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Serialize\JsonValidator
     * @since 2.2.0
     */
    private $jsonValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param array $additionalChecks
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param \Magento\Framework\Serialize\JsonValidator|null $jsonValidator
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        array $additionalChecks = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        \Magento\Framework\Serialize\JsonValidator $jsonValidator = null
    ) {
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        $this->additionalChecks = $additionalChecks;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->jsonValidator = $jsonValidator ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\JsonValidator::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $encryptor,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        $this->_init(\Magento\Quote\Model\ResourceModel\Quote\Payment::class);
    }

    /**
     * Declare quote model instance
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
     * @since 2.0.0
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote model instance
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Quote\Model\Quote
     * @since 2.0.0
     */
    public function getQuote()
    {
        return $this->_quote;
    }

    /**
     * Import data array to payment method object,
     * Method calls quote totals collect because payment method availability
     * can be related to quote totals
     *
     * @param array $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function importData(array $data)
    {
        $data = $this->convertPaymentData($data);
        $data = new \Magento\Framework\DataObject($data);
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_import_data_before',
            [$this->_eventObject => $this, 'input' => $data]
        );

        $this->setMethod($data->getMethod());
        $method = $this->getMethodInstance();
        $quote = $this->getQuote();

        /**
         * Payment availability related with quote totals.
         * We have to recollect quote totals before checking
         */
        $quote->collectTotals();

        $checks = array_merge($data->getChecks(), $this->additionalChecks);
        $methodSpecification = $this->methodSpecificationFactory->create($checks);
        if (!$method->isAvailable($quote) || !$methodSpecification->isApplicable($method, $quote)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The requested Payment Method is not available.')
            );
        }

        $method->assignData($data);

        /*
         * validating the payment data
         */
        $method->validate();
        return $this;
    }

    /**
     * Converts request to payment data
     *
     * @param array $rawData
     * @return array
     * @since 2.1.0
     */
    private function convertPaymentData(array $rawData)
    {
        $paymentData = [
            PaymentInterface::KEY_METHOD => null,
            PaymentInterface::KEY_PO_NUMBER => null,
            PaymentInterface::KEY_ADDITIONAL_DATA => [],
            'checks' => []
        ];

        foreach (array_keys($rawData) as $requestKey) {
            if (!array_key_exists($requestKey, $paymentData)) {
                $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA][$requestKey] = $rawData[$requestKey];
            } elseif ($requestKey === PaymentInterface::KEY_ADDITIONAL_DATA) {
                $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA] = array_merge(
                    $paymentData[PaymentInterface::KEY_ADDITIONAL_DATA],
                    (array) $rawData[$requestKey]
                );
            } else {
                $paymentData[$requestKey] = $rawData[$requestKey];
            }
        }

        return $paymentData;
    }

    /**
     * Prepare object for save
     *
     * @return $this
     * @since 2.0.0
     */
    public function beforeSave()
    {
        if ($this->getQuote()) {
            $this->setQuoteId($this->getQuote()->getId());
        }
        return parent::beforeSave();
    }

    /**
     * Checkout redirect URL getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getCheckoutRedirectUrl()
    {
        $method = $this->getMethodInstance();
        if ($method) {
            return $method->getCheckoutRedirectUrl();
        }
        return '';
    }

    /**
     * Checkout order place redirect URL getter
     *
     * @return string
     * @since 2.0.0
     */
    public function getOrderPlaceRedirectUrl()
    {
        $method = $this->getMethodInstance();
        if ($method) {
            return $method->getConfigData('order_place_redirect_url');
        }
        return '';
    }

    /**
     * Retrieve payment method model object
     *
     * @return \Magento\Payment\Model\MethodInterface
     * @since 2.0.0
     */
    public function getMethodInstance()
    {
        $method = parent::getMethodInstance();
        $method->setStore($this->getQuote()->getStoreId());
        return $method;
    }

    /**
     * @codeCoverageIgnoreStart
     */

    /**
     * Get purchase order number
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getPoNumber()
    {
        return $this->getData(self::KEY_PO_NUMBER);
    }

    /**
     * Set purchase order number
     *
     * @param string $poNumber
     * @return $this
     * @since 2.0.0
     */
    public function setPoNumber($poNumber)
    {
        return $this->setData(self::KEY_PO_NUMBER, $poNumber);
    }

    /**
     * Get payment method code
     *
     * @return string
     * @since 2.0.0
     */
    public function getMethod()
    {
        return $this->getData(self::KEY_METHOD);
    }

    /**
     * Set payment method code
     *
     * @param string $method
     * @return $this
     * @since 2.0.0
     */
    public function setMethod($method)
    {
        return $this->setData(self::KEY_METHOD, $method);
    }

    /**
     * Get payment additional details
     *
     * @return string[]|null
     * @since 2.0.0
     */
    public function getAdditionalData()
    {
        $additionalDataValue = $this->getData(self::KEY_ADDITIONAL_DATA);
        if (is_array($additionalDataValue)) {
            return $additionalDataValue;
        }
        if (is_string($additionalDataValue) && $this->jsonValidator->isValid($additionalDataValue)) {
            $additionalData = $this->serializer->unserialize($additionalDataValue);
            if (is_array($additionalData)) {
                return $additionalData;
            }
        }
        return null;
    }

    /**
     * Set payment additional details
     *
     * @param string $additionalData
     * @return $this
     * @since 2.0.0
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(self::KEY_ADDITIONAL_DATA, $additionalData);
    }

    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Quote\Api\Data\PaymentExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Quote\Api\Data\PaymentExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\PaymentExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
