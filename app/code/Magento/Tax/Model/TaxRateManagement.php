<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Tax\Api\TaxRateManagementInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;

class TaxRateManagement implements TaxRateManagementInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var TaxRuleRepositoryInterface
     */
    protected $taxRuleRepository;

    /**
     * @var TaxRateRepositoryInterface
     */
    protected $taxRateRepository;

    /**
     * @param TaxRuleRepositoryInterface $taxRuleRepository
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        TaxRuleRepositoryInterface $taxRuleRepository,
        TaxRateRepositoryInterface $taxRateRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->taxRuleRepository = $taxRuleRepository;
        $this->taxRateRepository = $taxRateRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getRatesByCustomerAndProductTaxClassId($customerTaxClassId, $productTaxClassId)
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('customer_tax_class_ids')
                    ->setValue([$customerTaxClassId])
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('product_tax_class_ids')
                    ->setValue([$productTaxClassId])
                    ->create(),
            ]
        );

        $searchResults = $this->taxRuleRepository->getList($this->searchCriteriaBuilder->create());
        $taxRules = $searchResults->getItems();
        $rates = [];
        foreach ($taxRules as $taxRule) {
            $rateIds = $taxRule->getTaxRateIds();
            if (!empty($rateIds)) {
                foreach ($rateIds as $rateId) {
                    $rates[] = $this->taxRateRepository->get($rateId);
                }
            }
        }
        return $rates;
    }
}
