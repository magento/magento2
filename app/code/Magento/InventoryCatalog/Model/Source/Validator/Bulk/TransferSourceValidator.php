<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\Source\Validator\Bulk;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryCatalogApi\Model\BulkInventoryTransferValidatorInterface;

/**
 * Check if sources exist and is available for transfer
 * Do not perform any source/product cross check
 */
class TransferSourceValidator implements BulkInventoryTransferValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceRepositoryInterface $sourceRepository
     * @SuppressWarnings(PHPMD.LongVariable)
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
    public function validate(array $skus, string $originSource, string $destinationSource): ValidationResult
    {
        $errors = [];

        try {
            $this->sourceRepository->get($originSource);
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Origin source %sourceCode does not exist', ['sourceCode' => $originSource]);
        }

        try {
            $this->sourceRepository->get($destinationSource);
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Destination source %sourceCode does not exist', ['sourceCode' => $destinationSource]);
        }

        if ($originSource === $destinationSource) {
            $errors[] = __('Cannot transfer a source on itself');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
