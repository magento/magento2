<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Model\Report;

use Braintree\MultipleValueNode;
use Braintree\RangeNode;
use Braintree\TextNode;
use Braintree\TransactionSearch;

class FilterMapper
{
    private $fieldsMap = [];

    private $conditionsMap = [];

    public function __construct()
    {
        $this->fieldsMap = [
            'id' => TransactionSearch::id(),
            'merchantAccountId' => TransactionSearch::merchantAccountId(),
            'orderId' => TransactionSearch::orderId(),
            'paypalDetails_paymentId' => TransactionSearch::paypalPaymentId(),
            'createdUsing' => TransactionSearch::createdUsing(),
            'type' => TransactionSearch::type(),
            'createdAt' => TransactionSearch::createdAt(),
            'amount' => TransactionSearch::amount(),
            'status' => TransactionSearch::status(),
            'settlementBatchId' => TransactionSearch::settlementBatchId(),
            'paymentInstrumentType' => TransactionSearch::paymentInstrumentType()
        ];

        $this->conditionsMap = [
            'Braintree\TextNode' => [
                'eq' => function (TextNode $field, $value) {
                    return $field->is($value);
                }
            ],
            'Braintree\RangeNode' => [
                'gteq' => function (RangeNode $field, $value) {
                    return $field->greaterThanOrEqualTo($value);
                },
                'lteq' => function (RangeNode $field, $value) {
                    return $field->lessThanOrEqualTo($value);
                }
            ],
            'Braintree\MultipleValueNode' => [
                'in' => function (MultipleValueNode $field, array $value) {
                    return $field->in(!is_array($value) ? [$value] : $value);
                },
                'eq' => function (MultipleValueNode $field, $value) {
                    return $field->is($value);
                }
            ]
        ];
    }

    /**
     * @param string $field
     * @param array $conditionMap
     * @return null|object
     */
    public function getFilter($field, array $conditionMap)
    {
        $fieldExpression = $this->getField($field);
        if (null === $fieldExpression) {
            return null;
        }

        if ($this->applyConditions($fieldExpression, $conditionMap)) {
            return $fieldExpression;
        }

        return null;
    }

    /**
     * @param string $field
     * @return object|null
     */
    private function getField($field)
    {
        if (!isset($this->fieldsMap[$field])) {
            return null;
        }

        return $this->fieldsMap[$field];
    }

    /**
     * @param object $field
     * @param string $conditionKey
     * @return null|\Closure
     */
    private function getCondition($field, $conditionKey)
    {
        if (
            !is_object($field)
            || !isset(
                $this->conditionsMap[get_class($field)],
                $this->conditionsMap[get_class($field)][$conditionKey]
            )
        ) {
            return null;
        }

        return $this->conditionsMap[get_class($field)][$conditionKey];
    }

    /**
     * @param object $fieldExpression
     * @param array $conditionMap
     * @return bool
     */
    private function applyConditions($fieldExpression, array $conditionMap)
    {
        $conditionsAppliedF = false;
        foreach ($conditionMap as $conditionKey => $value) {
            $condition = $this->getCondition($fieldExpression, $conditionKey);
            if (null !== $condition) {
                $conditionsAppliedF = true;
                $condition($fieldExpression, $value);
            }
        }

        return $conditionsAppliedF;
    }
}
