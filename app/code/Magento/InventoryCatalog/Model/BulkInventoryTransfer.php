<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalog\Model\ResourceModel\BulkInventoryTransfer as BulkInventoryTransferResource;
use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;
use Magento\InventoryCatalogApi\Model\BulkInventoryTransferValidatorInterface;
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
     * @var SourceIndexer
     */
    private $sourceIndexer;

    /**
     * @param BulkInventoryTransferValidatorInterface $inventoryTransferValidator
     * @param BulkInventoryTransferResource $bulkInventoryTransfer
     * @param SourceIndexer $sourceIndexer
     * @param null $defaultSourceProvider @deprecated
     * @param null $getProductIdsBySkus @deprecated
     * @param null $legacyIndexer @deprecated
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        BulkInventoryTransferValidatorInterface $inventoryTransferValidator,
        BulkInventoryTransferResource $bulkInventoryTransfer,
        SourceIndexer $sourceIndexer,
        $defaultSourceProvider,
        $getProductIdsBySkus,
        $legacyIndexer
    ) {
        $this->bulkInventoryTransferValidator = $inventoryTransferValidator;
        $this->bulkInventoryTransfer = $bulkInventoryTransfer;
        $this->sourceIndexer = $sourceIndexer;
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

        $this->bulkInventoryTransfer->execute(
            $skus,
            $originSource,
            $destinationSource,
            $unassignFromOrigin
        );

        $this->sourceIndexer->executeList([$originSource, $destinationSource]);

        return true;
    }
}
