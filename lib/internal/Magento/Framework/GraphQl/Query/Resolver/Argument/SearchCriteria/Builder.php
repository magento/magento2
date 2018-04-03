<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria;

use Magento\Framework\Api\Search\SearchCriteriaInterfaceFactory;
use Magento\Framework\Api\Search\SearchCriteriaInterface;

/**
 * Builder to create search criteria and apply corespondent filters from arguments
 */
class Builder
{
    /** @var SearchCriteriaInterfaceFactory */
    private $searchCriteriaFactory;

    /**
     * @var ArgumentApplierFactory
     */
    private $argumentApplierFactory;
    /**
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param ArgumentApplierFactory $argumentApplierFactory
     */
    public function __construct(
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        ArgumentApplierFactory $argumentApplierFactory
    ) {
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->argumentApplierFactory = $argumentApplierFactory;
    }

    /**
     * Build a search criteria and apply arguments to it as filters
     *
     * @param array $arguments
     * @return SearchCriteriaInterface
     */
    public function build(array $arguments) : SearchCriteriaInterface
    {
        $searchCriteria = $this->searchCriteriaFactory->create();
        foreach ($arguments as $argumentName => $argument) {
            $argumentApplier = $this->argumentApplierFactory->create($argumentName);
            $argumentApplier->applyArgument($searchCriteria, $argument);
        }
        return $searchCriteria;
    }
}
