<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Quote;

use Magento\Framework\Api\AttributeDataBuilder;

/**
 * Quote payment information
 *
 * @method \Magento\Sales\Model\Resource\Quote\Payment _getResource()
 * @method \Magento\Sales\Model\Resource\Quote\Payment getResource()
 * @method int getQuoteId()
 * @method \Magento\Sales\Model\Quote\Payment setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method \Magento\Sales\Model\Quote\Payment setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method \Magento\Sales\Model\Quote\Payment setUpdatedAt(string $value)
 * @method string getMethod()
 * @method \Magento\Sales\Model\Quote\Payment setMethod(string $value)
 * @method string getCcType()
 * @method \Magento\Sales\Model\Quote\Payment setCcType(string $value)
 * @method string getCcNumberEnc()
 * @method \Magento\Sales\Model\Quote\Payment setCcNumberEnc(string $value)
 * @method string getCcLast4()
 * @method \Magento\Sales\Model\Quote\Payment setCcLast4(string $value)
 * @method string getCcCidEnc()
 * @method \Magento\Sales\Model\Quote\Payment setCcCidEnc(string $value)
 * @method string getCcSsOwner()
 * @method \Magento\Sales\Model\Quote\Payment setCcSsOwner(string $value)
 * @method int getCcSsStartMonth()
 * @method \Magento\Sales\Model\Quote\Payment setCcSsStartMonth(int $value)
 * @method int getCcSsStartYear()
 * @method \Magento\Sales\Model\Quote\Payment setCcSsStartYear(int $value)
 * @method string getPoNumber()
 * @method \Magento\Sales\Model\Quote\Payment setPoNumber(string $value)
 * @method string getAdditionalData()
 * @method \Magento\Sales\Model\Quote\Payment setAdditionalData(string $value)
 * @method string getCcSsIssue()
 * @method \Magento\Sales\Model\Quote\Payment setCcSsIssue(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Payment extends \Magento\Payment\Model\Info
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
     * @var \Magento\Sales\Model\Quote
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
        $this->_init('Magento\Sales\Model\Resource\Quote\Payment');
    }

    /**
     * Declare quote model instance
     *
     * @param \Magento\Sales\Model\Quote $quote
     * @return $this
     */
    public function setQuote(\Magento\Sales\Model\Quote $quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }

    /**
     * Retrieve quote model instance
     *
     * @return \Magento\Sales\Model\Quote
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
}
