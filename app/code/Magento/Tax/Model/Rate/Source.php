<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Rate;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Convert\DataObject as Converter;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Model\Rate\Provider as RateProvider;

/**
 * Tax rate source model.
 * @since 2.0.0
 */
class Source implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $options;

    /**
     * @var \Magento\Tax\Api\TaxRateRepositoryInterface
     * @since 2.0.0
     */
    protected $taxRateRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     * @since 2.0.0
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     * @since 2.0.0
     */
    protected $converter;

    /**
     * @var \Magento\Tax\Model\Rate\Provider
     * @since 2.2.0
     */
    protected $rateProvider;

    /**
     * Initialize dependencies.
     *
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Converter $converter
     * @param RateProvider $rateProvider
     * @since 2.0.0
     */
    public function __construct(
        TaxRateRepositoryInterface $taxRateRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Converter $converter,
        RateProvider $rateProvider = null
    ) {
        $this->taxRateRepository = $taxRateRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->converter = $converter;
        $this->rateProvider = $rateProvider ?: ObjectManager::getInstance()->get(RateProvider::class);
    }

    /**
     * Retrieve all tax rates as an options array.
     *
     * @return array
     * @since 2.0.0
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
