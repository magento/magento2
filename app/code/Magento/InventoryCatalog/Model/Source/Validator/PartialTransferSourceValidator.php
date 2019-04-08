<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryCatalog\Model\GetSourceItemsBySkuAndSourceCodes;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferInterface;
use Magento\InventoryCatalogApi\Model\PartialInventoryTransferValidatorInterface;

class PartialTransferSourceValidator implements PartialInventoryTransferValidatorInterface
{
    /** @var ValidationResultFactory */
    private $validationResultFactory;

    /** @var SourceRepositoryInterface */
    private $sourceRepository;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceRepositoryInterface $sourceRepository
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        SourceRepositoryInterface $sourceRepository
    ) {
        $this->validationResultFactory  = $validationResultFactory;
        $this->sourceRepository         = $sourceRepository;
    }

    /**
     * Validates a partial transfer request.
     *
     * @param PartialInventoryTransferInterface $transfer
     * @return ValidationResult
     */
    public function validate(PartialInventoryTransferInterface $transfer): ValidationResult
    {
        $errors = [];

        try {
            $this->sourceRepository->get($transfer->getOriginSourceCode());
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Origin source %sourceCode does not exist', ['sourceCode' => $transfer->getOriginSourceCode()]);
        }

        try {
            $this->sourceRepository->get($transfer->getDestinationSourceCode());
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Destination source %sourceCode does not exist', ['sourceCode' => $transfer->getDestinationSourceCode()]);
        }

        if ($transfer->getOriginSourceCode() === $transfer->getDestinationSourceCode()) {
            $errors[] = __('Cannot transfer a source on itself');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}