<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\SalesRule\Observer\CatalogAttributeSaveAfterObserver
 *
 * @since 2.0.0
 */
class CatalogAttributeSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\SalesRule\Observer\CheckSalesRulesAvailability
     * @since 2.0.0
     */
    protected $checkSalesRulesAvailability;

    /**
     * @param CheckSalesRulesAvailability $checkSalesRulesAvailability
     * @since 2.0.0
     */
    public function __construct(
        \Magento\SalesRule\Observer\CheckSalesRulesAvailability $checkSalesRulesAvailability
    ) {
        $this->checkSalesRulesAvailability = $checkSalesRulesAvailability;
    }

    /**
     * After save attribute if it is not used for promo rules already check rules for containing this attribute
     *
     * @param EventObserver $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        if ($attribute->dataHasChangedFor('is_used_for_promo_rules') && !$attribute->getIsUsedForPromoRules()) {
            $this->checkSalesRulesAvailability->checkSalesRulesAvailability($attribute->getAttributeCode());
        }

        return $this;
    }
}
