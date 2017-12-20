<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Controller\Adminhtml\Stock;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Inventory\Model\StockSourceLink;
use Magento\Inventory\Model\StockSourceLinkFactory;
use Magento\InventoryApi\Api\AssignSourcesToStockInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;
use Magento\InventoryApi\Api\UnassignSourceFromStockInterface;

/**
 * At the time of processing Stock save form this class used to save links correctly
 * Performs replace strategy of sources for the stock
 */
class StockSourceLinkProcessor
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StockSourceLinkFactory
     */
    private $stockSourceLinkFactory;

    /**
     * @var GetAssignedSourcesForStockInterface
     */
    private $getAssignedSourcesForStock;

    /**
     * @var AssignSourcesToStockInterface
     */
    private $assignSourcesToStock;

    /**
     * @var UnassignSourceFromStockInterface
     */
    private $unassignSourceFromStock;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StockSourceLinkFactory $stockSourceLinkFactory
     * @param GetAssignedSourcesForStockInterface $getAssignedSourcesForStock
     * @param AssignSourcesToStockInterface $assignSourcesToStock
     * @param UnassignSourceFromStockInterface $unassignSourceFromStock
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockSourceLinkFactory $stockSourceLinkFactory,
        GetAssignedSourcesForStockInterface $getAssignedSourcesForStock,
        AssignSourcesToStockInterface $assignSourcesToStock,
        UnassignSourceFromStockInterface $unassignSourceFromStock
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockSourceLinkFactory = $stockSourceLinkFactory;
        $this->getAssignedSourcesForStock = $getAssignedSourcesForStock;
        $this->assignSourcesToStock = $assignSourcesToStock;
        $this->unassignSourceFromStock = $unassignSourceFromStock;
    }

    /**
     * @param int $stockId
     * @param array $stockSourceLinksData
     * @return void
     * @throws InputException
     */
    public function process(int $stockId, array $stockSourceLinksData)
    {
        $this->validateStockSourceData($stockSourceLinksData);

        $assignedSources = $this->getAssignedSourcesForStock->execute($stockId);
        $sourceCodesForSave = array_flip(array_column($stockSourceLinksData, StockSourceLink::SOURCE_CODE));
        $sourceCodesForDelete = [];

        foreach ($assignedSources as $assignedSource) {
            if (array_key_exists($assignedSource->getCode(), $sourceCodesForSave)) {
                unset($sourceCodesForSave[$assignedSource->getCode()]);
            } else {
                $sourceCodesForDelete[] = $assignedSource->getCode();
            }
        }

        if ($sourceCodesForSave) {
            $this->assignSourcesToStock->execute(array_keys($sourceCodesForSave), $stockId);
        }
        if ($sourceCodesForDelete) {
            foreach ($sourceCodesForDelete as $sourceCodeForDelete) {
                $this->unassignSourceFromStock->execute($sourceCodeForDelete, $stockId);
            }
        }
    }

    /**
     * @param array $stockSourceLinksData
     * @return void
     * @throws InputException
     */
    private function validateStockSourceData(array $stockSourceLinksData)
    {
        foreach ($stockSourceLinksData as $stockSourceLinkData) {
            if (!isset($stockSourceLinkData[StockSourceLink::SOURCE_CODE])) {
                throw new InputException(__('Wrong Stock to Source relation parameters given.'));
            }
        }
    }
}
