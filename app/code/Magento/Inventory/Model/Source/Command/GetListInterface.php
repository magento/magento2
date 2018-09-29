<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Source\Command;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\SourceSearchResultsInterface;

/**
 * Find Sources by SearchCriteria command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial GetList call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * @see \Magento\InventoryApi\Api\SourceRepositoryInterface
 * @api
 */
interface GetListInterface
{
    /**
     * Find Sources by given SearchCriteria
     * SearchCriteria is not required because load all sources is useful case
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return SourceSearchResultsInterface
     */
    public function execute(SearchCriteriaInterface $searchCriteria = null): SourceSearchResultsInterface;
}
