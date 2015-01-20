<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Model\Quote;

use Magento\Framework\Api\AttributeDataBuilder;

/**
 * Quote payment information
 *
 * @method \Magento\Quote\Model\Resource\Quote\Payment _getResource()
 * @method \Magento\Quote\Model\Resource\Quote\Payment getResource()
 * @method int getQuoteId()
 * @method \Magento\Quote\Model\Quote\Payment setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Quote\Model\Quote\Payment setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Quote\Model\Quote\Payment setUpdatedAt(string $value)
 * @method \Magento\Quote\Model\Quote\Payment setMethod(string $value)
 * @method \Magento\Quote\Model\Quote\Payment setCcType(string $value)
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
 * @method \Magento\Quote\Model\Quote\Payment setPoNumber(string $value)
 * @method \Magento\Quote\Model\Quote\Payment setAdditionalData(string $value)
 * @method string getCcSsIssue()
 * @method \Magento\Quote\Model\Quote\Payment setCcSsIssue(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Payment extends \Magento\Payment\Model\Info implements \Magento\Quote\Api\Data\PaymentInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_quote_payment';

    /**
     * @var string
     */
    protected $_eventObject = 'payment';

    /**
     * Quote model object
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     */
    protected $methodSpecificationFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Payment\Model\Checks\SpecificationFactory $methodSpecificationFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->methodSpecificationFactory = $methodSpecificationFactory;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
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
     */
    protected function _construct()
    {
        $this->_init('Magento\Quote\Model\Resource\Quote\Payment');
    }

    /**
     * Declare quote model instance
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return $this
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
     * @return \Magento\Quote\Model\Quote
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
     * @throws \Magento\Framework\Model\Exception
     */
    public function importData(array $data)
    {
        $data = new \Magento\Framework\Object($data);
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

        $methodSpecification = $this->methodSpecificationFactory->create($data->getChecks());
        if (!$method->isAvailable($quote) || !$methodSpecification->isApplicable($method, $quote)) {
            throw new \Magento\Framework\Model\Exception(__('The requested Payment Method is not available.'));
        }

        $method->assignData($data);
        /*
         * validating the payment data
         */
        $method->validate();
        return $this;
    }

    /**
     * Prepare object for save
     *
     * @return $this
     */
    public function beforeSave()
    {
        if ($this->getQuote()) {
            $this->setQuoteId($this->getQuote()->getId());
        }
        try {
            $method = $this->getMethodInstance();
        } catch (\Magento\Framework\Model\Exception $e) {
            return parent::beforeSave();
        }
        $method->prepareSave();
        return parent::beforeSave();
    }

    /**
     * Checkout redirect URL getter
     *
     * @return string
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
     */
    public function getOrderPlaceRedirectUrl()
    {
        $method = $this->getMethodInstance();
        if ($method) {
            return $method->getOrderPlaceRedirectUrl();
        }
        return '';
    }

    /**
     * Retrieve payment method model object
     *
     * @return \Magento\Payment\Model\MethodInterface
     */
    public function getMethodInstance()
    {
        $method = parent::getMethodInstance();
        return $method->setStore($this->getQuote()->getStore());
    }

    /**
     * @codeCoverageIgnoreStart
     *
     * {@inheritdoc}
     */
    public function getPoNumber()
    {
        return $this->getData('po_number');
    }

    /**
     * {@inheritdoc}
     */
    public function getMethod()
    {
        return $this->getData('method');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcOwner()
    {
        return $this->getData('cc_owner');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcNumber()
    {
        return $this->getData('cc_number');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcType()
    {
        return $this->getData('cc_type');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcExpYear()
    {
        return $this->getData('cc_exp_year');
    }

    /**
     * {@inheritdoc}
     */
    public function getCcExpMonth()
    {
        return $this->getData('cc_exp_month');
    }

    /**
     * {@inheritdoc}
     */
    public function getAdditionalData()
    {
        $additionalDataValue = $this->getData('additional_data');
        if (is_string($additionalDataValue)) {
            $additionalData = @unserialize($additionalDataValue);
            if (is_array($additionalData)) {
                return $additionalData;
            }
        } elseif (is_array($additionalDataValue)) {
            return $additionalDataValue;
        }
        return null;
    }
    //@codeCoverageIgnoreEnd
}
