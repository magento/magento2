<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplier;

use Magento\Framework\GraphQl\Argument\AstConverterInterface;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Argument\Filter\FilterArgumentValueInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\FilterGroupFactory;
use Magento\Framework\Phrase;

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
    public function applyArgument(SearchCriteriaInterface $searchCriteria, $argument)
    {
        $filters = $this->astConverter->convert(\Magento\Catalog\Model\Product::ENTITY, $argument);
        if (!empty($filters)) {
            $filterGroups = $searchCriteria->getFilterGroups();
            $filterGroups = array_merge($filterGroups, $this->filterGroupFactory->create($filters));
            $searchCriteria->setFilterGroups($filterGroups);
        } else {
            throw new \Magento\Framework\Exception\RuntimeException(
                new Phrase('Argument %1 not of type %2', [$argument->getName(), FilterArgumentValueInterface::class])
            );
        }
    }
}
