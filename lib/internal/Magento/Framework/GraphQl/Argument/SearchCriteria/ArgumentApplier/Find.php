<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplier;

use Magento\Framework\GraphQl\ArgumentInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\GraphQl\Argument\Find\FindArgumentValueInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\GraphQl\Argument\SearchCriteria\FilterGroupFactory;
use Magento\Framework\Phrase;

/**
 * Class for Find Argument
 */
class Find implements ArgumentApplierInterface
{
    /**
     * @var FilterGroupFactory
     */
    private $filterGroupFactory;

    /**
     * @param FilterGroupFactory|null $filterGroupFactory
     */
    public function __construct($filterGroupFactory = null)
    {
        $this->filterGroupFactory = $filterGroupFactory ?: ObjectManager::getInstance()
            ->get(FilterGroupFactory::class);
    }

    /**
     * {@inheritdoc}
     */
    public function applyArgument(SearchCriteriaInterface $searchCriteria, ArgumentInterface $argument)
    {
        $filter = $argument->getValue();
        if ($filter instanceof FindArgumentValueInterface) {
            $searchCriteria->setFilterGroups($this->filterGroupFactory->create($filter));
        } else {
            throw new \Magento\Framework\Exception\RuntimeException(
                new Phrase('Argument %1 not of type %2', $argument->getName(), FindArgumentValueInterface::class)
            );
        }
    }
}
