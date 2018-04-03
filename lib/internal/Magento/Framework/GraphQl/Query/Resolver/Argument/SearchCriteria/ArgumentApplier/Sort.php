<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplierInterface;
use Magento\Framework\Phrase;

/**
 * Class for Sort Argument
 */
class Sort implements ArgumentApplierInterface
{
    const ARGUMENT_NAME = 'sort';

    /** @var SortOrderBuilder */
    private $sortOrderBuilder;

    /**
     * @param SortOrderBuilder|null $sortOrderBuilder
     */
    public function __construct(SortOrderBuilder $sortOrderBuilder = null)
    {
        $this->sortOrderBuilder = $sortOrderBuilder ?: ObjectManager::getInstance()
            ->get(SortOrderBuilder::class);
    }

    /**
     * {@inheritdoc}
     */
    public function applyArgument(SearchCriteriaInterface $searchCriteria, $argument) : SearchCriteriaInterface
    {
        if (is_array($argument)) {
            $sortOrders = [];
            foreach ($argument as $fieldName => $fieldValue) {
                /** @var SortOrder $sortOrder */
                $sortOrders[] = $this->sortOrderBuilder->setField($fieldName)
                    ->setDirection($fieldValue == 'DESC' ? SortOrder::SORT_DESC : SortOrder::SORT_ASC)
                    ->create();
            }
            $searchCriteria->setSortOrders($sortOrders);
        } elseif (!empty($argument)) {
            throw new \Magento\Framework\Exception\RuntimeException(
                new Phrase('Argument %1 not of type array or null', [$argument])
            );
        }
        return $searchCriteria;
    }
}
