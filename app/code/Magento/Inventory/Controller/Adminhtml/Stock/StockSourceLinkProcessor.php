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
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryApi\Api\StockSourceLinksDeleteInterface;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;
use Magento\InventoryApi\Api\GetAssignedSourcesForStockInterface;

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
     * @var StockSourceLinksSaveInterface
     */
    private $stockSourceLinksSave;

    /**
     * @var StockSourceLinksDeleteInterface
     */
    private $stockSourceLinksDelete;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StockSourceLinkFactory $stockSourceLinkFactory
     * @param GetAssignedSourcesForStockInterface $getAssignedSourcesForStock
     * @param StockSourceLinksSaveInterface $stockSourceLinksSave
     * @param StockSourceLinksDeleteInterface $stockSourceLinksDelete
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockSourceLinkFactory $stockSourceLinkFactory,
        GetAssignedSourcesForStockInterface $getAssignedSourcesForStock,
        StockSourceLinksSaveInterface $stockSourceLinksSave,
        StockSourceLinksDeleteInterface $stockSourceLinksDelete
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockSourceLinkFactory = $stockSourceLinkFactory;
        $this->getAssignedSourcesForStock = $getAssignedSourcesForStock;
        $this->stockSourceLinksSave = $stockSourceLinksSave;
        $this->stockSourceLinksDelete = $stockSourceLinksDelete;
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
            if (array_key_exists($assignedSource->getSourceCode(), $sourceCodesForSave)) {
                unset($sourceCodesForSave[$assignedSource->getSourceCode()]);
            } else {
                $sourceCodesForDelete[] = $assignedSource->getSourceCode();
            }
        }

        if (count($sourceCodesForSave) > 0) {
            $this->stockSourceLinksSave->execute($this->getStockSourceLinks(array_keys($sourceCodesForSave), $stockId));
        }

        if (count($sourceCodesForDelete) > 0) {
            $this->stockSourceLinksDelete->execute($this->getStockSourceLinks($sourceCodesForDelete, $stockId));
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

    /**
     * Map link information to StockSourceLinkInterface multiply
     *
     * @param array $sourceCodeList
     * @param int $stockId
     * @return StockSourceLinkInterface[]
     */
    private function getStockSourceLinks(array $sourceCodeList, $stockId): array
    {
        $linkList = [];

        foreach ($sourceCodeList as $sourceCode) {
            /** @var StockSourceLinkInterface $linkData */
            $linkData = $this->stockSourceLinkFactory->create();

            $linkData->setSourceCode($sourceCode);
            $linkData->setStockId($stockId);

            $linkList[] = $linkData;
        }

        return $linkList;
    }

}
