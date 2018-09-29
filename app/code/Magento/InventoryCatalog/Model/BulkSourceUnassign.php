<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalogApi\Api\BulkSourceUnassignInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\BulkSourceUnassignValidatorInterface;
use Magento\InventoryCatalog\Model\ResourceModel\BulkSourceUnassign as BulkSourceUnassignResource;
use Magento\InventoryIndexer\Indexer\Source\SourceIndexer;
use Magento\CatalogInventory\Model\Indexer\Stock as LegacyIndexer;

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
     * MassProductSourceAssign constructor.
     * @param BulkSourceUnassignValidatorInterface $unassignValidator
     * @param BulkSourceUnassignResource $bulkSourceUnassign
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param GetProductIdsBySkus $getProductIdsBySkus
     * @param SourceIndexer $sourceIndexer
     * @param LegacyIndexer $legacyIndexer
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkSourceUnassignValidatorInterface $unassignValidator,
        BulkSourceUnassignResource $bulkSourceUnassign,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetProductIdsBySkus $getProductIdsBySkus,
        SourceIndexer $sourceIndexer,
        LegacyIndexer $legacyIndexer
    ) {
        $this->unassignValidator = $unassignValidator;
        $this->bulkSourceUnassign = $bulkSourceUnassign;
        $this->sourceIndexer = $sourceIndexer;
        $this->legacyIndexer = $legacyIndexer;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
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

        $res = $this->bulkSourceUnassign->execute($skus, $sourceCodes);

        $this->sourceIndexer->executeList($sourceCodes);
        if (in_array($this->defaultSourceProvider->getCode(), $sourceCodes, true)) {
            $this->reindexLegacy($skus);
        }

        return $res;
    }
}
