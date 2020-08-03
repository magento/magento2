<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\SalesRule\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Api\Data\RuleInterface;

/**
 * Search and return Sales rule by name.
 */
class GetSalesRuleByName
{
    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RuleRepositoryInterface $ruleRepository
     */
    public function __construct(SearchCriteriaBuilder $searchCriteriaBuilder, RuleRepositoryInterface $ruleRepository)
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Return Sales Rule by name.
     *
     * @param string $name
     * @return RuleInterface|null
     */
    public function execute(string $name): ?RuleInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('name', $name)->create();
        $salesRules = $this->ruleRepository->getList($searchCriteria)->getItems();

        return array_shift($salesRules);
    }
}
