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
     * @var MassAssignResource
     */
    private $massAssignResource;

    /**
     * MassProductSourceAssign constructor.
     * @param MassAssignValidatorInterface $assignValidator
     * @param MassAssignResource $massAssignResource
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        MassAssignValidatorInterface $assignValidator,
        MassAssignResource $massAssignResource
    ) {
        $this->assignValidator = $assignValidator;
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

        // TODO: Trigger reindex?
        return $this->massAssignResource->execute($skus, $sourceCodes);
    }
}
