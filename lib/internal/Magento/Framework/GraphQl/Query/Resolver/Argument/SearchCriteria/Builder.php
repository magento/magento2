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
     * @var ArgumentApplierPool
     */
    private $argumentApplierPool;
    /**
     * @param SearchCriteriaInterfaceFactory $searchCriteriaFactory
     * @param ArgumentApplierPool $ArgumentApplierPool
     */
    public function __construct(
        SearchCriteriaInterfaceFactory $searchCriteriaFactory,
        ArgumentApplierPool $ArgumentApplierPool
    ) {
        $this->searchCriteriaFactory = $searchCriteriaFactory;
        $this->argumentApplierPool = $ArgumentApplierPool;
    }

    /**
     * Build a search criteria and apply arguments to it as filters
     *
     * @param string $fieldName
     * @param array $arguments
     * @return SearchCriteriaInterface
     */
    public function build(string $fieldName, array $arguments) : SearchCriteriaInterface
    {
        $searchCriteria = $this->searchCriteriaFactory->create();
        foreach ($arguments as $argumentName => $argument) {
            if ($this->argumentApplierPool->hasApplier($argumentName)) {
                $argumentApplier = $this->argumentApplierPool->getApplier($argumentName);
                $argumentApplier->applyArgument($searchCriteria, $fieldName, $argumentName, $argument);
            }
        }
        return $searchCriteria;
    }
}
