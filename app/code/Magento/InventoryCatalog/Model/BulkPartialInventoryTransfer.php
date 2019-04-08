<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\CatalogInventory\Model\Indexer\Stock as LegacyIndexer;
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
    ) {
        $this->transferValidator = $partialInventoryTransferValidator;
        $this->transferCommand = $transferInventoryPartiallyCommand;
        $this->productIdsBySkus = $getProductIdsBySkus;
        $this->sourceItemsBySku = $getSourceItemsBySkuAndSourceCodes;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->sourceIndexer = $sourceIndexer;
        $this->legacyIndexer = $legacyIndexer;
    }

    /**
     * Run bulk partial inventory transfer for specified items.
     *
     * @param PartialInventoryTransferInterface $transfer
     * @return SourceItemInterface[]
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute($transfer): array
    {
        $validationResult = $this->transferValidator->validate($transfer);
        if ($validationResult->isValid()) {
            return $this->processTransfer($transfer);
        }

        throw new ValidationException(__("Transfer validation failed"), null, 0, $validationResult);
    }

    /**
     * @param PartialInventoryTransferInterface $transfer
     * @return SourceItemInterface[]
     */
    private function processTransfer($transfer): array
    {
        $processedSkus = [];
        $sourceItems = [];

        foreach ($transfer->getItems() as $item) {
            $this->transferCommand->execute($item, $transfer->getOriginSourceCode(), $transfer->getDestinationSourceCode());
            $processedSkus[] = $item->getSku();
            $sourceItems += $this->sourceItemsBySku->execute($item->getSku(), [$transfer->getOriginSourceCode(), $transfer->getDestinationSourceCode()]);
        }

        $this->updateIndexes([$transfer->getOriginSourceCode(), $transfer->getDestinationSourceCode()], $processedSkus);
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
     *
     * @param string[] $skus
     */
    private function updateLegacyIndex(array $skus)
    {
        $productIds = $this->productIdsBySkus->execute($skus);
        $this->legacyIndexer->executeList($productIds);
    }
}