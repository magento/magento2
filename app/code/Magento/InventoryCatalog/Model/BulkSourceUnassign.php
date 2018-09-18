<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalogApi\Api\BulkSourceUnassignInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\BulkSourceUnassignValidatorInterface;
use Magento\InventoryCatalog\Model\ResourceModel\BulkSourceUnassign as BulkSourceUnassignResource;
use Magento\InventoryIndexer\Indexer\Source\SourceIndexer;
use Magento\CatalogInventory\Model\Indexer\Stock as LegacyIndexer;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;

/**
 * @inheritdoc
 */
class BulkSourceUnassign implements BulkSourceUnassignInterface
{
    /**
     * @var BulkSourceUnassignValidatorInterface
     */
    private $unassignValidator;

    /**
     * @var BulkSourceUnassignResource
     */
    private $bulkSourceUnassign;

    /**
     * @var SourceIndexer
     */
    private $sourceIndexer;

    /**
     * @var LegacyIndexer
     */
    private $legacyIndexer;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var GetProductIdsBySkus
     */
    private $getProductIdsBySkus;

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
     * @param BulkSourceUnassignValidatorInterface $unassignValidator
     * @param BulkSourceUnassignResource $bulkSourceUnassign
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param GetProductIdsBySkus $getProductIdsBySkus
     * @param SourceIndexer $sourceIndexer
     * @param LegacyIndexer $legacyIndexer
     * @param EventManagerInterface $eventManager
     * @param DataObjectFactory $dataObjectFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkSourceUnassignValidatorInterface $unassignValidator,
        BulkSourceUnassignResource $bulkSourceUnassign,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetProductIdsBySkus $getProductIdsBySkus,
        SourceIndexer $sourceIndexer,
        LegacyIndexer $legacyIndexer,
        EventManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->unassignValidator = $unassignValidator;
        $this->bulkSourceUnassign = $bulkSourceUnassign;
        $this->sourceIndexer = $sourceIndexer;
        $this->legacyIndexer = $legacyIndexer;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Reindex legacy stock (for default source)
     * @param array $skus
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function reindexLegacy(array $skus): void
    {
        $productIds = array_values($this->getProductIdsBySkus->execute($skus));
        $this->legacyIndexer->executeList($productIds);
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(array $skus, array $sourceCodes): int
    {
        $validationResult = $this->unassignValidator->validate($skus, $sourceCodes);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        $operation = $this->dataObjectFactory->create(['data' => [
            'skus' => $skus,
            'source_codes' => $sourceCodes,
        ]]);

        $this->eventManager->dispatch('inventory_bulk_source_unassign_before', ['operation' => $operation]);
        $res = $this->bulkSourceUnassign->execute($skus, $sourceCodes);
        $this->eventManager->dispatch('inventory_bulk_source_unassign_after', ['operation' => $operation]);

        $this->sourceIndexer->executeList($sourceCodes);
        if (in_array($this->defaultSourceProvider->getCode(), $sourceCodes, true)) {
            $this->reindexLegacy($skus);
        }

        return $res;
    }
}
