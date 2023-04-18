<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Rate;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Convert\DataObject as Converter;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Model\Rate\Provider as RateProvider;

/**
 * Tax rate source model.
 */
class Source implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Initialize dependencies.
     *
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Converter $converter
     * @param RateProvider $rateProvider
     */
    public function __construct(
        protected readonly TaxRateRepositoryInterface $taxRateRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly Converter $converter,
        protected ?RateProvider $rateProvider = null
    ) {
        $this->rateProvider = $rateProvider ?: ObjectManager::getInstance()->get(RateProvider::class);
    }

    /**
     * Retrieve all tax rates as an options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->setPageSize($this->rateProvider->getPageSize())
                ->setCurrentPage(1)
                ->create();

            $this->options = $this->rateProvider->toOptionArray($searchCriteria);
        }

        return $this->options;
    }
}
