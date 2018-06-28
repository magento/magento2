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
use Magento\InventoryIndexer\Indexer\InventoryIndexer;

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
     * @var InventoryIndexer
     */
    private $inventoryIndexer;

    /**
     * MassProductSourceAssign constructor.
     * @param BulkInventoryTransferValidatorInterface $inventoryTransferValidator
     * @param BulkInventoryTransferResource $bulkInventoryTransfer
     * @param InventoryIndexer $inventoryIndexer
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkInventoryTransferValidatorInterface $inventoryTransferValidator,
        BulkInventoryTransferResource $bulkInventoryTransfer,
        InventoryIndexer $inventoryIndexer
    ) {
        $this->bulkInventoryTransferValidator = $inventoryTransferValidator;
        $this->bulkInventoryTransfer = $bulkInventoryTransfer;
        $this->inventoryIndexer = $inventoryIndexer;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus, string $destinationSource, bool $defaultSourceOnly = false): void
    {
        $validationResult = $this->bulkInventoryTransferValidator->validate(
            $skus,
            $destinationSource,
            $defaultSourceOnly
        );

        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        $this->bulkInventoryTransfer->execute(
            $skus,
            $destinationSource,
            $defaultSourceOnly
        );

        $this->inventoryIndexer->executeFull();
    }
}
