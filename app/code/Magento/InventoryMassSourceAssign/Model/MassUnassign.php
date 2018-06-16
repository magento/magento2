<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssign\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryMassSourceAssignApi\Api\MassUnassignInterface;
use Magento\InventoryMassSourceAssignApi\Model\MassUnassignValidatorInterface;
use Magento\InventoryMassSourceAssign\Model\ResourceModel\MassUnassign as MassUnassignResource;

/**
 * @inheritdoc
 */
class MassUnassign implements MassUnassignInterface
{
    /**
     * @var MassUnassignValidatorInterface
     */
    private $unassignValidator;

    /**
     * @var MassUnassignResource
     */
    private $massUnassignResource;

    /**
     * MassProductSourceAssign constructor.
     * @param MassUnassignValidatorInterface $unassignValidator
     * @param MassUnassignResource $massUnassignResource
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        MassUnassignValidatorInterface $unassignValidator,
        MassUnassignResource $massUnassignResource
    ) {
        $this->unassignValidator = $unassignValidator;
        $this->massUnassignResource = $massUnassignResource;
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
        return $this->massUnassignResource->execute($skus, $sourceCodes);
    }
}
