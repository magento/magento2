<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier;

use Magento\Framework\GraphQl\Query\Resolver\Argument\AstConverter;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\FilterGroupFactory;
use Magento\Framework\GraphQl\Query\Resolver\Argument\Filter\ConnectiveFactory;

/**
 * Class for Filter Argument
 */
class Filter implements ArgumentApplierInterface
{
    const ARGUMENT_NAME = 'filter';

    /**
     * @var FilterGroupFactory
     */
    private $filterGroupFactory;

    /**
     * @var AstConverter
     */
    private $astConverter;

    /**
     * @var ConnectiveFactory
     */
    private $connectiveFactory;

    /**
     * @param AstConverter $astConverter
     * @param FilterGroupFactory $filterGroupFactory
     * @param ConnectiveFactory $connectiveFactory
     */
    public function __construct(
        AstConverter $astConverter,
        FilterGroupFactory $filterGroupFactory,
        ConnectiveFactory $connectiveFactory
    ) {
        $this->astConverter = $astConverter;
        $this->filterGroupFactory = $filterGroupFactory;
        $this->connectiveFactory = $connectiveFactory;
    }

    /**
     * @inheritDoc
     */
    public function applyArgument(
        SearchCriteriaInterface $searchCriteria,
        string $fieldName,
        string $argumentName,
        array $argument
    ) : SearchCriteriaInterface {
        $filters = $this->astConverter->getClausesFromAst($fieldName, $argument);
        $filtersForGroup = $this->connectiveFactory->create($filters);
        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups = array_merge($filterGroups, $this->filterGroupFactory->create($filtersForGroup));
        $searchCriteria->setFilterGroups($filterGroups);
        return $searchCriteria;
    }
}
