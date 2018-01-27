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
use Magento\InventoryApi\Api\GetSourceLinksInterface;
use Magento\InventoryApi\Api\StockSourceLinksDeleteInterface;
use Magento\InventoryApi\Api\StockSourceLinksSaveInterface;

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
     * @var StockSourceLinksSaveInterface
     */
    private $stockSourceLinksSave;

    /**
     * @var StockSourceLinksDeleteInterface
     */
    private $stockSourceLinksDelete;

    /**
     * @var GetSourceLinksInterface
     */
    private $getSourceLinks;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StockSourceLinkFactory $stockSourceLinkFactory
     * @param StockSourceLinksSaveInterface $stockSourceLinksSave
     * @param StockSourceLinksDeleteInterface $stockSourceLinksDelete
     * @param GetSourceLinksInterface $getSourceLinks
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StockSourceLinkFactory $stockSourceLinkFactory,
        StockSourceLinksSaveInterface $stockSourceLinksSave,
        StockSourceLinksDeleteInterface $stockSourceLinksDelete,
        GetSourceLinksInterface $getSourceLinks
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->stockSourceLinkFactory = $stockSourceLinkFactory;
        $this->stockSourceLinksSave = $stockSourceLinksSave;
        $this->stockSourceLinksDelete = $stockSourceLinksDelete;
        $this->getSourceLinks = $getSourceLinks;
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

        $assignedLinks = $this->getAssignedLinks($stockId);
        $linksDataToSave = $this->processStockSourceLinksData($stockSourceLinksData);

        $linksToDelete = [];
        $assignedSourceCodes = [];

        foreach ($assignedLinks as $assignedLink) {
            $assignedSourceCodes[] = $assignedLink->getSourceCode();
            if (array_key_exists($assignedLink->getSourceCode(), $linksDataToSave)) {
                continue;
            }
            $linksToDelete[] = $assignedLink;
        }

        if (count($linksToDelete) > 0) {
            $this->stockSourceLinksDelete->execute($linksToDelete);
        }

        $linksToSave = [];

        foreach ($linksDataToSave as $sourceCodeToSave => $linkDataToSave) {
            if (in_array($sourceCodeToSave, $assignedSourceCodes)) {
                continue;
            }
            $linksToSave[] = $this->stockSourceLinkFactory->create([
                'data' => [
                    StockSourceLink::SOURCE_CODE => $sourceCodeToSave,
                    StockSourceLink::STOCK_ID => $stockId,
                    StockSourceLink::PRIORITY => $linkDataToSave[StockSourceLink::PRIORITY],
                ]
            ]);
        }

        if (count($linksToSave) > 0) {
            $this->stockSourceLinksSave->execute($linksToSave);
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
     * Retrieves links that are assigned to $stockId
     *
     * @param int $stockId
     * @return StockSourceLinkInterface[]
     */
    private function getAssignedLinks(int $stockId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(StockSourceLinkInterface::STOCK_ID, $stockId)
            ->create();

        $searchResult = $this->getSourceLinks->execute($searchCriteria);

        return $searchResult->getItems();
    }

    /**
     * @param array $stockSourceLinksData
     *
     * @return array
     */
    private function processStockSourceLinksData($stockSourceLinksData): array
    {
        $result = [];

        foreach ($stockSourceLinksData as $stockSourceLinkData) {
            $sourceCode = $stockSourceLinkData[StockSourceLinkInterface::SOURCE_CODE];

            $result[$sourceCode] = $stockSourceLinkData;
        }

        return $result;
    }
}
