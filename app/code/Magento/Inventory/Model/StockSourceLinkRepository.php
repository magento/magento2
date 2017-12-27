<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\StockSourceLink\Command\GetListInterface;
use Magento\Inventory\Model\StockSourceLink\Command\SaveInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkResultsInterface;
use Magento\InventoryApi\Api\StockSourceLinkRepositoryInterface;

/**
 * @inheritdoc
 */
class StockSourceLinkRepository implements StockSourceLinkRepositoryInterface
{
    /**
     * @var GetListInterface
     */
    private $commandGetList;

    /**
     * @var SaveInterface
     */
    private $commandSave;

    /**
     * @param GetListInterface $commandGetList
     * @param StockSourceLink\Command\SaveInterface $commandSave
     */
    public function __construct(
        GetListInterface $commandGetList,
        SaveInterface $commandSave
    ) {
        $this->commandGetList = $commandGetList;
        $this->commandSave = $commandSave;
    }

    /**
     * @inheritdoc
     */
    public function save(StockSourceLinkInterface $stock): int
    {
        return $this->commandSave->execute($stock);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): StockSourceLinkResultsInterface
    {
        return $this->commandGetList->execute($searchCriteria);
    }
}
