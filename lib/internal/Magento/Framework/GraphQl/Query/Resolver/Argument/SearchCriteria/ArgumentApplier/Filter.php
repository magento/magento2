<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier;

use Magento\Framework\GraphQl\Query\Resolver\Argument\AstConverterInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\FilterGroupFactory;

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
     * @var AstConverterInterface
     */
    private $astConverter;

    /**
     * @param AstConverterInterface $astConverter
     * @param FilterGroupFactory $filterGroupFactory
     */
    public function __construct(AstConverterInterface $astConverter, FilterGroupFactory $filterGroupFactory)
    {
        $this->astConverter = $astConverter;
        $this->filterGroupFactory = $filterGroupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function applyArgument(SearchCriteriaInterface $searchCriteria, $argument) : SearchCriteriaInterface
    {
        $filters = $this->astConverter->convert($argument);
        $filterGroups = $searchCriteria->getFilterGroups();
        $filterGroups = array_merge($filterGroups, $this->filterGroupFactory->create($filters));
        $searchCriteria->setFilterGroups($filterGroups);
        return $searchCriteria;
    }
}
