<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Rate;

use Magento\Framework\Convert\DataObject as Converter;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Tax\Model\Calculation\Rate;

/**
 * Provides filtered tax rates models
 * as options for select element.
 */
class Provider
{
    /**
     * @var TaxRateRepositoryInterface
     */
    private $taxRateRepository;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var int
     */
    private $pageSize = 100;

    /**
     * Initialize dependencies.
     *
     * @param TaxRateRepositoryInterface $taxRateRepository
     * @param Converter $converter
     */
    public function __construct(
        TaxRateRepositoryInterface $taxRateRepository,
        Converter $converter
    ) {
        $this->taxRateRepository = $taxRateRepository;
        $this->converter = $converter;
    }

    /**
     * Retrieve all tax rates as an options array.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    public function toOptionArray(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->taxRateRepository->getList($searchCriteria);

        return $this->converter->toOptionArray(
            $searchResults->getItems(),
            Rate::KEY_ID,
            Rate::KEY_CODE
        );
    }

    /**
     * Returns predefined size of tax rates list
     *
     * @return int
     */
    public function getPageSize()
    {
        return (int) $this->pageSize;
    }
}
