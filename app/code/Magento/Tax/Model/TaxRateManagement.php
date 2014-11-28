<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Tax\Model;

use Magento\Tax\Api\TaxRateManagementInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;

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
        $this->searchCriteriaBuilder->addFilter(
            [
                $this->filterBuilder
                    ->setField('customer_tax_class_ids')
                    ->setValue([$customerTaxClassId])
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilter(
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
