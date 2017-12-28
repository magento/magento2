<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkSearchResultsInterface;
use Magento\InventoryApi\Api\GetSourceLinkListInterface;
use Magento\InventoryApi\Api\StockSourceLinkRepositoryInterface;
use Magento\InventoryApi\Api\StockSourceLinksDeleteInterface;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;

/**
 * @inheritdoc
 */
class StockSourceLinkRepository implements StockSourceLinkRepositoryInterface
{
    /**
     * @var GetSourceLinkListInterface
     */
    private $commandGetList;

    /**
     * @var StockSourceLinksSaveInterface
     */
    private $commandSave;

    /**
     * @var StockSourceLinksDeleteInterface
     */
    private $commandDelete;

    /**
     * @param GetSourceLinkListInterface $commandGetList
     * @param StockSourceLinksSaveInterface $commandSave
     * @param StockSourceLinksDeleteInterface $commandDelete
     */
    public function __construct(
        GetSourceLinkListInterface $commandGetList,
        StockSourceLinksSaveInterface $commandSave,
        StockSourceLinksDeleteInterface $commandDelete
    ) {
        $this->commandGetList = $commandGetList;
        $this->commandSave = $commandSave;
        $this->commandDelete = $commandDelete;
    }

    /**
     * @inheritdoc
     */
    public function save(array $links): array
    {
        return $this->commandSave->execute($links);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StockSourceLinkSearchResultsInterface
    {
        return $this->commandGetList->execute($searchCriteria);
    }

    /**
     * @inheritdoc
     */
    public function delete(array $links)
    {
        $this->commandDelete->execute($links);
    }
}
