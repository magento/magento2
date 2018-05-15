<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesAdminUi\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\GetStockSourceLinksInterface;

/**
 * Sugar service for find StockSourceLinks by source code
 */
class GetStockSourceLinksBySourceCode
{
    /**
     * @var GetStockSourceLinksInterface
     */
    private $getStockSourceLinksInterface;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param GetStockSourceLinksInterface $getStockSourceLinksInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        GetStockSourceLinksInterface $getStockSourceLinksInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->getStockSourceLinksInterface = $getStockSourceLinksInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string $sourceCode
     * @return StockSourceLinkInterface[]
     */
    public function execute(string $sourceCode): array
    {
        $this->searchCriteriaBuilder->addFilter(StockSourceLinkInterface::SOURCE_CODE, $sourceCode);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->getStockSourceLinksInterface->execute($searchCriteria)->getItems();
    }
}
