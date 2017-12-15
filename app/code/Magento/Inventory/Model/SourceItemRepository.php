<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Inventory\Model\SourceItem\Command\GetListInterface;
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
     * @param GetListInterface $commandGetList
     */
    public function __construct(
        GetListInterface $commandGetList
    ) {
        $this->commandGetList = $commandGetList;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SourceItemSearchResultsInterface
    {
        return $this->commandGetList->execute($searchCriteria);
    }
}
