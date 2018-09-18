<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

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
     * MassProductSourceAssign constructor.
     * @param BulkSourceAssignValidatorInterface $assignValidator
     * @param BulkSourceAssignResource $bulkSourceAssign
     * @param SourceItemIndexer $sourceItemIndexer
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkSourceAssignValidatorInterface $assignValidator,
        BulkSourceAssignResource $bulkSourceAssign,
        SourceItemIndexer $sourceItemIndexer
    ) {
        $this->assignValidator = $assignValidator;
        $this->bulkSourceAssign = $bulkSourceAssign;
        $this->sourceItemIndexer = $sourceItemIndexer;
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

        $res = $this->bulkSourceAssign->execute($skus, $sourceCodes);
        $this->sourceItemIndexer->executeList($sourceCodes);

        return $res;
    }
}
