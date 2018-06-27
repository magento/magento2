<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalogApi\Api\BulkSourceUnassignInterface;
use Magento\InventoryCatalogApi\Model\BulkSourceUnassignValidatorInterface;
use Magento\InventoryCatalog\Model\ResourceModel\BulkSourceUnassign as BulkSourceUnassignResource;

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
     * MassProductSourceAssign constructor.
     * @param BulkSourceUnassignValidatorInterface $unassignValidator
     * @param BulkSourceUnassignResource $bulkSourceUnassign
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkSourceUnassignValidatorInterface $unassignValidator,
        BulkSourceUnassignResource $bulkSourceUnassign
    ) {
        $this->unassignValidator = $unassignValidator;
        $this->bulkSourceUnassign = $bulkSourceUnassign;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $skus, array $sourceCodes): int
    {
        $validationResult = $this->unassignValidator->validate($skus, $sourceCodes);
        if (!$validationResult->isValid()) {
            throw new ValidationException(__('Validation Failed'), null, 0, $validationResult);
        }

        // TODO: Trigger reindex?
        return $this->bulkSourceUnassign->execute($skus, $sourceCodes);
    }
}
