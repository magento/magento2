<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\SourceItem\Command\DeleteInterface;
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
     * @var DeleteInterface
     */
    private $commandDelete;

    /**
     * @var GetListInterface
     */
    private $commandGetList;

    /**
     * @param DeleteInterface $commandDelete
     * @param GetListInterface $commandGetList
     */
    public function __construct(
        DeleteInterface $commandDelete,
        GetListInterface $commandGetList
    ) {
        $this->commandDelete = $commandDelete;
        $this->commandGetList = $commandGetList;
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
    public function delete(SourceItemInterface $sourceItem)
    {
        $this->commandDelete->execute($sourceItem);
    }
}
