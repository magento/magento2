<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;

/**
 * Helper class for testing cart price rule conditions.
 */
trait ConditionHelper
{
    /**
     * Gets quote by reserved order id.
     *
     * @param string $reservedOrderId
     * @return CartInterface
     */
    private function getQuote($reservedOrderId)
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('reserved_order_id', $reservedOrderId)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $quoteRepository = $this->objectManager->get(CartRepositoryInterface::class);
        $items = $quoteRepository->getList($searchCriteria)->getItems();
        return array_pop($items);
    }

    /**
     * Gets rule by name.
     *
     * @param string $name
     * @return \Magento\SalesRule\Model\Rule
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getSalesRule(string $name): \Magento\SalesRule\Model\Rule
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('name', $name)
            ->create();

        /** @var CartRepositoryInterface $quoteRepository */
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $items = $ruleRepository->getList($searchCriteria)->getItems();

        $rule = array_pop($items);
        /** @var \Magento\SalesRule\Model\Converter\ToModel $converter */
        $converter = $this->objectManager->get(\Magento\SalesRule\Model\Converter\ToModel::class);

        return $converter->toModel($rule);
    }
}
