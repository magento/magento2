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
use Magento\InventoryCatalogApi\Api\BulkSourceAssignInterface;
use Magento\InventoryCatalogApi\Model\BulkSourceAssignValidatorInterface;
use Magento\InventoryCatalog\Model\ResourceModel\BulkSourceAssign as BulkSourceAssignResource;
use Magento\InventoryIndexer\Indexer\SourceItem\SourceItemIndexer;

/**
 * @inheritdoc
 */
class BulkSourceAssign implements BulkSourceAssignInterface
{
    /**
     * @var BulkSourceAssignValidatorInterface
     */
    private $assignValidator;

    /**
     * @var BulkSourceAssignResource
     */
    private $bulkSourceAssign;

    /**
     * @var SourceItemIndexer
     */
    private $sourceItemIndexer;

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
     * @param BulkSourceAssignValidatorInterface $assignValidator
     * @param BulkSourceAssignResource $bulkSourceAssign
     * @param EventManagerInterface $eventManager
     * @param SourceItemIndexer $sourceItemIndexer
     * @param DataObjectFactory $dataObjectFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkSourceAssignValidatorInterface $assignValidator,
        BulkSourceAssignResource $bulkSourceAssign,
        EventManagerInterface $eventManager,
        SourceItemIndexer $sourceItemIndexer,
        DataObjectFactory $dataObjectFactory
    ) {
        $this->assignValidator = $assignValidator;
        $this->bulkSourceAssign = $bulkSourceAssign;
        $this->sourceItemIndexer = $sourceItemIndexer;
        $this->eventManager = $eventManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus, array $sourceCodes): int
    {
        $validationResult = $this->assignValidator->validate($skus, $sourceCodes);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        $operation = $this->dataObjectFactory->create(['data' => [
            'skus' => $skus,
            'source_codes' => $sourceCodes,
        ]]);

        $this->eventManager->dispatch('inventory_bulk_source_assign_before', ['operation' => $operation]);
        $res = $this->bulkSourceAssign->execute($operation->getData('skus'), $operation->getData('source_codes'));
        $this->eventManager->dispatch('inventory_bulk_source_assign_after', ['operation' => $operation]);
        $this->sourceItemIndexer->executeList($sourceCodes);

        return $res;
    }
}
