<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria;

use Magento\Framework\GraphQl\ArgumentInterface;
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
     * @param ArgumentInterface[] $arguments
     * @return SearchCriteriaInterface
     */
    public function build(array $arguments)
    {
        $searchCriteria = $this->searchCriteriaFactory->create();
        foreach ($arguments as $argument) {
            $argumentApplier = $this->argumentApplierFactory->create($argument->getName());
            $argumentApplier->applyArgument($searchCriteria, $argument);
        }
        return $searchCriteria;
    }
}
