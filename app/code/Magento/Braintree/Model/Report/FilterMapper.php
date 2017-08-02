<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Report;

use Magento\Braintree\Model\Adapter\BraintreeSearchAdapter;
use Magento\Braintree\Model\Report\ConditionAppliers\AppliersPool;

/**
 * Class FilterMapper
 * @since 2.1.0
 */
class FilterMapper
{
    /**
     * @var array
     * @since 2.1.0
     */
    private $searchFieldsToFiltersMap = [];

    /**
     * @var \Magento\Braintree\Model\Report\ConditionAppliers\AppliersPool
     * @since 2.1.0
     */
    private $appliersPool;

    /**
     * @var \Magento\Braintree\Model\Adapter\BraintreeSearchAdapter
     * @since 2.1.0
     */
    private $braintreeSearchAdapter;

    /**
     * @since 2.1.0
     */
    public function __construct(
        AppliersPool $appliersPool,
        BraintreeSearchAdapter $braintreeSearchAdapter
    ) {
        $this->appliersPool = $appliersPool;
        $this->braintreeSearchAdapter = $braintreeSearchAdapter;
        $this->initFieldsToFiltersMap();
    }

    /**
     * Init fields map with Braintree filters
     * @return void
     * @since 2.1.0
     */
    private function initFieldsToFiltersMap()
    {
        $this->searchFieldsToFiltersMap = [
            'id' => $this->braintreeSearchAdapter->id(),
            'merchantAccountId' => $this->braintreeSearchAdapter->merchantAccountId(),
            'orderId' => $this->braintreeSearchAdapter->orderId(),
            'paypalDetails_paymentId' => $this->braintreeSearchAdapter->paypalPaymentId(),
            'createdUsing' => $this->braintreeSearchAdapter->createdUsing(),
            'type' => $this->braintreeSearchAdapter->type(),
            'createdAt' => $this->braintreeSearchAdapter->createdAt(),
            'amount' => $this->braintreeSearchAdapter->amount(),
            'status' => $this->braintreeSearchAdapter->status(),
            'settlementBatchId' => $this->braintreeSearchAdapter->settlementBatchId(),
            'paymentInstrumentType' => $this->braintreeSearchAdapter->paymentInstrumentType()
        ];
    }

    /**
     * Get filter with applied conditions
     * @param string $field
     * @param array $conditionMap
     * @return null|object
     * @since 2.1.0
     */
    public function getFilter($field, array $conditionMap)
    {
        if (!isset($this->searchFieldsToFiltersMap[$field])) {
            return null;
        }

        $fieldFilter = $this->searchFieldsToFiltersMap[$field];
        if ($this->applyConditions($fieldFilter, $conditionMap)) {
            return $fieldFilter;
        }

        return null;
    }

    /**
     * Apply conditions to filter
     *
     * @param object $fieldFilter
     * @param array $conditionMap
     * @return bool
     * @since 2.1.0
     */
    private function applyConditions($fieldFilter, array $conditionMap)
    {
        $applier = $this->appliersPool->getApplier($fieldFilter);

        $conditionsAppliedCounter = 0;
        foreach ($conditionMap as $conditionKey => $value) {
            if ($applier->apply($fieldFilter, $conditionKey, $value)) {
                $conditionsAppliedCounter ++;
            }
        }

        return $conditionsAppliedCounter > 0;
    }
}
