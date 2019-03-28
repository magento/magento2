<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\CatalogInventory\Model\Indexer\Stock as LegacyIndexer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalog\Model\ResourceModel\TransferInventoryPartially;
use Magento\InventoryCatalogApi\Api\BulkPartialInventoryTransferInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalogApi\Model\PartialInventoryTransferValidatorInterface;
use Magento\InventoryIndexer\Indexer\Source\SourceIndexer;

class BulkPartialInventoryTransfer implements BulkPartialInventoryTransferInterface
{
    /** @var PartialInventoryTransferValidatorInterface  */
    private $transferValidator;

    /** @var TransferInventoryPartially  */
    private $transferCommand;

    /** @var GetProductIdsBySkusInterface  */
    private $productIdsBySkus;

    /** @var GetSourceItemsBySkuAndSourceCodes  */
    private $sourceItemsBySku;

    /** @var DefaultSourceProviderInterface  */
    private $defaultSourceProvider;

    /** @var SourceIndexer  */
    private $sourceIndexer;

    /** @var LegacyIndexer  */
    private $legacyIndexer;

    /**
     * BulkPartialInventoryTransfer constructor.
     * @param PartialInventoryTransferValidatorInterface $partialInventoryTransferValidator
     * @param TransferInventoryPartially $transferInventoryPartiallyCommand
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param GetSourceItemsBySkuAndSourceCodes $getSourceItemsBySkuAndSourceCodes
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SourceIndexer $sourceIndexer
     * @param LegacyIndexer $legacyIndexer
     */
    public function __construct(
        PartialInventoryTransferValidatorInterface $partialInventoryTransferValidator,
        TransferInventoryPartially $transferInventoryPartiallyCommand,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        GetSourceItemsBySkuAndSourceCodes $getSourceItemsBySkuAndSourceCodes,
        DefaultSourceProviderInterface $defaultSourceProvider,
        SourceIndexer $sourceIndexer,
        LegacyIndexer $legacyIndexer
    )
    {
        $this->transferValidator = $partialInventoryTransferValidator;
        $this->transferCommand   = $transferInventoryPartiallyCommand;
        $this->productIdsBySkus  = $getProductIdsBySkus;
        $this->sourceItemsBySku  = $getSourceItemsBySkuAndSourceCodes;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->sourceIndexer     = $sourceIndexer;
        $this->legacyIndexer     = $legacyIndexer;
    }

    /**
     * Run bulk partial inventory transfer for specified items.
     *
     * @param PartialInventoryTransferInterface[] $items
     * @return SourceItemInterface[]
     */
    public function execute(array $items): array
    {
        $sources = [];
        $processedSkus = [];
        $sourceItems = [];

        foreach ($items as $item) {
            $validationResult = $this->transferValidator->validate($item);
            if ($validationResult->isValid()) {
                $this->transferCommand->execute($item);

                $processedSkus[] = $item->getSku();
                $sources[] = $item->getOriginSourceCode();
                $sources[] = $item->getDestinationSourceCode();
            }
            $sourceItems += $this->sourceItemsBySku->execute($item->getSku(), [$item->getOriginSourceCode(), $item->getDestinationSourceCode()]);
        }

        $this->updateIndexes($sources, $processedSkus);
        return $sourceItems;
    }

    /**
     * @param string[] $sources
     * @param string[] $skus
     */
    private function updateIndexes(array $sources, array $skus)
    {
        $sources = array_unique($sources);
        $this->sourceIndexer->executeList($sources);

        if (in_array($this->defaultSourceProvider->getCode(), $sources)) {
            $this->updateLegacyIndex($skus);
        }
    }

    /**
     * @param string[] $skus
     */
    private function updateLegacyIndex(array $skus)
    {
        try {
            $productIds = $this->productIdsBySkus->execute($skus);
            $this->legacyIndexer->executeList($productIds);
        } catch (NoSuchEntityException $e) {}
    }
}