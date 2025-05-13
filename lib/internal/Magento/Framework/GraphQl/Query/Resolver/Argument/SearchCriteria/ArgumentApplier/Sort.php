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
    public function __construct(?SortOrderBuilder $sortOrderBuilder = null)
    {
        $this->sortOrderBuilder = $sortOrderBuilder ?: ObjectManager::getInstance()
            ->get(SortOrderBuilder::class);
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
        $sortOrders = [];
        foreach ($argument as $fieldName => $fieldValue) {
            /** @var SortOrder $sortOrder */
            $sortOrders[] = $this->sortOrderBuilder->setField($fieldName)
                ->setDirection($fieldValue == 'DESC' ? SortOrder::SORT_DESC : SortOrder::SORT_ASC)
                ->create();
        }
        $searchCriteria->setSortOrders($sortOrders);

        return $searchCriteria;
    }
}
