<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Rate;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\Object as Converter;
use Magento\Tax\Api\Data\TaxRateInterface as TaxRate;
use Magento\Tax\Api\TaxRateRepositoryInterface;

/**
 * Tax rate source model.
 */
class Source implements \Magento\Framework\Data\OptionSourceInterface
{
    /** @var array */
    protected $options;

    /** @var TaxRateRepositoryInterface */
    protected $taxRateRepository;

    /** @var SearchCriteriaBuilder */
    protected $searchCriteriaBuilder;

    /** @var Converter */
    protected $converter;

    /**
     * Initialize dependencies.
     *
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Converter $converter
     */
    public function __construct(
        TaxRateRepositoryInterface $taxRateRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Converter $converter
    ) {
        $this->taxRateRepository = $taxRateRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->converter = $converter;
    }

    /**
     * Retrieve all tax rates as an options array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $searchResults = $this->taxRateRepository->getList($searchCriteria);
            $this->options = $this->converter->toOptionArray(
                $searchResults->getItems(),
                TaxRate::KEY_ID,
                TaxRate::KEY_CODE
            );
        }
        return $this->options;
    }
}
