<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
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
        $this->validationResultFactory = $validationResultFactory;
        $this->sourceRepository = $sourceRepository;
    }

    /**
     * @inheritdoc
     */
    public function validate(string $originSourceCode, string $destinationSourceCode, array $items): ValidationResult
    {
        $errors = [];

        try {
            $this->sourceRepository->get($originSourceCode);
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Origin source %sourceCode does not exist', ['sourceCode' => $originSourceCode]);
        }

        try {
            $this->sourceRepository->get($destinationSourceCode);
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Destination source %sourceCode does not exist', ['sourceCode' => $destinationSourceCode]);
        }

        if ($originSourceCode === $destinationSourceCode) {
            $errors[] = __('Cannot transfer a source on itself');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}