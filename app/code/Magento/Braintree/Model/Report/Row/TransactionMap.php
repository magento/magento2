<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Report\Row;

use Braintree\Transaction;
use DateTime;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\Search\DocumentInterface;

/**
 * Class TransactionMap
 * @since 2.1.0
 */
class TransactionMap implements DocumentInterface
{
    const TRANSACTION_FIELD_MAP_DELIMITER = '_';

    /**
     * @var AttributeValueFactory
     * @since 2.1.0
     */
    private $attributeValueFactory;

    /**
     * @var Transaction
     * @since 2.1.0
     */
    private $transaction;

    /**
     * @var array
     * @since 2.1.0
     */
    public static $simpleFieldsMap = [
        'id',
        'merchantAccountId',
        'orderId',
        'paymentInstrumentType',
        'paypalDetails_paymentId',
        'type',
        'createdAt',
        'amount',
        'processorSettlementResponseCode',
        'status',
        'processorSettlementResponseText',
        'refundIds',
        'settlementBatchId',
        'currencyIsoCode'
    ];

    /**
     * @param AttributeValueFactory $attributeValueFactory
     * @param Transaction $transaction
     * @since 2.1.0
     */
    public function __construct(
        AttributeValueFactory $attributeValueFactory,
        Transaction $transaction
    ) {
        $this->attributeValueFactory = $attributeValueFactory;
        $this->transaction = $transaction;
    }

    /**
     * Get Id
     *
     * @return string
     * @since 2.1.0
     */
    public function getId()
    {
        return $this->getMappedValue('id');
    }

    /**
     * Set Id
     *
     * @param int $id
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function setId($id)
    {
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null
     * @since 2.1.0
     */
    public function getCustomAttribute($attributeCode)
    {
        /** @var \Magento\Framework\Api\AttributeInterface $attributeValue */
        $attributeValue = $this->attributeValueFactory->create();
        $attributeValue->setAttributeCode($attributeCode);
        $attributeValue->setValue($this->getMappedValue($attributeCode));
        return $attributeValue;
    }

    /**
     * Set an attribute value for a given attribute code
     *
     * @param string $attributeCode
     * @param mixed $attributeValue
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        return $this;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     * @since 2.1.0
     */
    public function getCustomAttributes()
    {
        $shouldBeLocalized = ['paymentInstrumentType', 'type', 'status'];
        $output = [];
        foreach ($this->getMappedValues() as $key => $value) {
            $attribute = $this->attributeValueFactory->create();
            if (in_array($key, $shouldBeLocalized)) {
                $value = __($value);
            }
            $output[] = $attribute->setAttributeCode($key)->setValue($value);
        }
        return $output;
    }

    /**
     * Set array of custom attributes
     *
     * @param \Magento\Framework\Api\AttributeInterface[] $attributes
     * @return $this
     * @throws \LogicException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.1.0
     */
    public function setCustomAttributes(array $attributes)
    {
        return $this;
    }

    /**
     * Get mapped value
     *
     * @param string $key
     * @return mixed
     * @since 2.1.0
     */
    private function getMappedValue($key)
    {
        if (!in_array($key, static::$simpleFieldsMap)) {
            return null;
        }

        $val = $this->getTransactionFieldValue($key);
        $val = $this->convertToText($val);
        return $val;
    }

    /**
     * @return array
     * @since 2.1.0
     */
    private function getMappedValues()
    {
        $result = [];

        foreach (static::$simpleFieldsMap as $key) {
            $val = $this->getTransactionFieldValue($key);
            $val = $this->convertToText($val);
            $result[$key] = $val;
        }

        return $result;
    }

    /**
     * Recursive get transaction field value
     *
     * @param string $key
     * @return Transaction|mixed|null
     * @since 2.1.0
     */
    private function getTransactionFieldValue($key)
    {
        $keys = explode(self::TRANSACTION_FIELD_MAP_DELIMITER, $key);
        $result = $this->transaction;
        foreach ($keys as $k) {
            if (!isset($result->$k)) {
                $result = null;
                break;
            }
            $result = $result->$k;
        }
        return $result;
    }

    /**
     * Convert value to text representation
     *
     * @param string $val
     * @return string
     * @since 2.1.0
     */
    private function convertToText($val)
    {
        if (is_object($val)) {
            switch (get_class($val)) {
                case 'DateTime':
                    /** @var DateTime $val */
                    $val = $val->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
            }
        } elseif (is_array($val)) {
            $val = implode(', ', $val);
        }

        return (string) $val;
    }
}
