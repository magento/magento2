<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Report\Row;

use Braintree\Transaction;
use DateTime;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\Search\DocumentInterface;

class TransactionMap implements DocumentInterface
{
    /**
     * @var AttributeValueFactory
     */
    private $attributeValueFactory;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var \Closure
     */
    private $simpleFieldReader;

    /**
     * @var array
     */
    private $mappedValues = [];

    /**
     * @var array
     */
    public static $simpleFieldsMap = [
        'id',
        'merchantAccountId',
        'orderId',
        'paymentInstrumentType',
        'type',
        'createdAt',
        'amount',
        'processorSettlementResponseCode',
        'status',
        'processorSettlementResponseText',
        'refundIds',
        'settlementBatchId'
    ];

    /**
     * @param AttributeValueFactory $attributeValueFactory
     * @param Transaction $transaction
     */
    public function __construct(
        AttributeValueFactory $attributeValueFactory,
        Transaction $transaction
    ) {
        $this->attributeValueFactory = $attributeValueFactory;
        $this->transaction = $transaction;

        $this->simpleFieldReader = function ($key) use ($transaction) {
            return $transaction->$key;
        };

        foreach (self::$simpleFieldsMap as $key) {
            $this->mappedValues[$key] = $this->simpleFieldReader;
        }
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getMappedValue('id');
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId($id)
    {
    }

    /**
     * Get an attribute value.
     *
     * @param string $attributeCode
     * @return \Magento\Framework\Api\AttributeInterface|null
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
     */
    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        return $this;
    }

    /**
     * Retrieve custom attributes values.
     *
     * @return \Magento\Framework\Api\AttributeInterface[]|null
     */
    public function getCustomAttributes()
    {
        $output = [];
        foreach ($this->getMappedValues() as $key => $value) {
            $attribute = $this->attributeValueFactory->create();
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
     */
    public function setCustomAttributes(array $attributes)
    {
        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    private function getMappedValue($key)
    {
        if (!isset($this->mappedValues[$key])) {
            return null;
        }

        return $this->mappedValues[$key]();
    }

    /**
     * @return array
     */
    private function getMappedValues()
    {
        $result = [];
        foreach ($this->mappedValues as $key => $callback) {
            $val = $callback($key);

            if (is_object($val)) {
                switch (get_class($val)) {
                    case 'DateTime':
                        /** @var DateTime $val */
                        $val = $val->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
                }
            }

            $result[$key] = $val;
        }

        return $result;
    }
}
