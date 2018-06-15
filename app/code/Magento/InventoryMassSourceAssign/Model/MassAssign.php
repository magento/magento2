<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssign\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryMassSourceAssignApi\Api\MassAssignInterface;
use Magento\InventoryMassSourceAssignApi\Model\MassAssignValidatorInterface;
use Magento\InventoryMassSourceAssign\Model\ResourceModel\MassAssign as MassAssignResource;

/**
 * @inheritdoc
 */
class MassAssign implements MassAssignInterface
{
    /**
     * @var MassAssignValidatorInterface
     */
    private $assignValidator;

    /**
     * @var SourceItemsBuilder
     */
    private $sourceItemsBuilder;

    /**
     * @var MassAssignResource
     */
    private $massAssignResource;

    /**
     * MassProductSourceAssign constructor.
     * @param MassAssignValidatorInterface $assignValidator
     * @param SourceItemsBuilder $sourceItemsBuilder
     * @param MassAssignResource $massAssignResource
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        MassAssignValidatorInterface $assignValidator,
        SourceItemsBuilder $sourceItemsBuilder,
        MassAssignResource $massAssignResource
    ) {
        $this->assignValidator = $assignValidator;
        $this->sourceItemsBuilder = $sourceItemsBuilder;
        $this->massAssignResource = $massAssignResource;
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

        $sourceItems = $this->sourceItemsBuilder->create($skus, $sourceCodes);

        // TODO: Trigger reindex?
        return $this->massAssignResource->execute($sourceItems);
    }
}
