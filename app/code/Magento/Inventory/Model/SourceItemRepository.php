<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\SourceItem\Command\GetListInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceItemSearchResultsInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;

/**
 * @inheritdoc
 */
class SourceItemRepository implements SourceItemRepositoryInterface
{
    /**
     * @var GetListInterface
     */
    private $commandGetList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param GetListInterface $commandGetList
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        GetListInterface $commandGetList,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->commandGetList = $commandGetList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SourceItemSearchResultsInterface
    {
        return $this->commandGetList->execute($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function getBySku(string $sku): SourceItemSearchResultsInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $sku)
            ->create();

        return $this->getList($searchCriteria);
    }
}
