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
     * MassProductSourceAssign constructor.
     * @param BulkSourceUnassignValidatorInterface $unassignValidator
     * @param BulkSourceUnassignResource $bulkSourceUnassign
     * @param DefaultSourceProviderInterface $defaultSourceProvider @deprecated
     * @param GetProductIdsBySkus $getProductIdsBySkus @deprecated
     * @param SourceIndexer $sourceIndexer
     * @param LegacyIndexer $legacyIndexer @deprecated
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
    }

    /**
     * @inheritdoc
     * @param array $skus
     * @param array $sourceCodes
     * @return int
     * @throws ValidationException
     */
    public function execute(array $skus, array $sourceCodes): int
    {
        $validationResult = $this->unassignValidator->validate($skus, $sourceCodes);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        $res = $this->bulkSourceUnassign->execute($skus, $sourceCodes);

        $this->sourceIndexer->executeList($sourceCodes);
        return $res;
    }
}
