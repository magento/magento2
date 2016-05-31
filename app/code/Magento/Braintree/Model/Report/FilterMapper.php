<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Report;

use Magento\Braintree\Model\Adapter\BraintreeSearchAdapter;
use Magento\Braintree\Model\Report\ConditionAppliers\AppliersPool;

/**
 * Class FilterMapper
 */
class FilterMapper
{
    /**
     * @var array
     */
    private $searchFieldsToFiltersMap = [];

    /** @var AppliersPool */
    private $appliersPool;

    /** @var BraintreeSearchAdapter */
    private $braintreeSearchAdapter;

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
