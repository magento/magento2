<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalog\Model\ResourceModel\BulkInventoryTransfer as BulkInventoryTransferResource;
use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\BulkInventoryTransferValidatorInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\CatalogInventory\Model\Indexer\Stock as LegacyIndexer;
use Magento\InventoryIndexer\Indexer\Source\SourceIndexer;

/**
 * @inheritdoc
 */
class BulkInventoryTransfer implements BulkInventoryTransferInterface
{
    /**
     * @var BulkInventoryTransferValidatorInterface
     */
    private $bulkInventoryTransferValidator;

    /**
     * @var BulkInventoryTransfer
     */
    private $bulkInventoryTransfer;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var LegacyIndexer
     */
    private $legacyIndexer;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SourceIndexer
     */
    private $sourceIndexer;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * MassProductSourceAssign constructor.
     * @param BulkInventoryTransferValidatorInterface $inventoryTransferValidator
     * @param BulkInventoryTransferResource $bulkInventoryTransfer
     * @param SourceIndexer $sourceIndexer
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param LegacyIndexer $legacyIndexer
     * @param EventManagerInterface $eventManager
     * @param DataObjectFactory $dataObjectFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkInventoryTransferValidatorInterface $inventoryTransferValidator,
        BulkInventoryTransferResource $bulkInventoryTransfer,
        SourceIndexer $sourceIndexer,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        LegacyIndexer $legacyIndexer,
        EventManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->bulkInventoryTransferValidator = $inventoryTransferValidator;
        $this->bulkInventoryTransfer = $bulkInventoryTransfer;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->legacyIndexer = $legacyIndexer;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->sourceIndexer = $sourceIndexer;
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Reindex legacy stock (for default source)
     * @param array $productIds
     */
    private function reindexLegacy(array $productIds): void
    {
        $this->legacyIndexer->executeList($productIds);
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignFromOrigin
    ): bool {
        $validationResult = $this->bulkInventoryTransferValidator->validate(
            $skus,
            $originSource,
            $destinationSource
        );

        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        $operation = $this->dataObjectFactory->create(['data' => [
            'skus' => $skus,
            'origin_source' => $originSource,
            'destination_source' => $destinationSource,
            'unassign_from_origin' => $unassignFromOrigin,
        ]]);

        $this->eventManager->dispatch('inventory_bulk_transfer_before', ['operation' => $operation]);
        $this->bulkInventoryTransfer->execute(
            $skus,
            $originSource,
            $destinationSource,
            $unassignFromOrigin
        );
        $this->eventManager->dispatch('inventory_bulk_transfer_after', ['operation' => $operation]);

        $this->sourceIndexer->executeList([$originSource, $destinationSource]);

        if (($this->defaultSourceProvider->getCode() === $originSource) ||
            ($this->defaultSourceProvider->getCode() === $destinationSource)) {
            $productIds = array_values($this->getProductIdsBySkus->execute($skus));
            $this->reindexLegacy($productIds);
        }

        return true;
    }
}
