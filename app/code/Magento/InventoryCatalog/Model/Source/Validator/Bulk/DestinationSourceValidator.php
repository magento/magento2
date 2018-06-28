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
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\BulkInventoryTransferValidatorInterface;

/**
 * Check if sources exist and is available for transfer
 * Do not perform any source/product cross check
 */
class DestinationSourceValidator implements BulkInventoryTransferValidatorInterface
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
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceRepositoryInterface $sourceRepository
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        SourceRepositoryInterface $sourceRepository,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->sourceRepository = $sourceRepository;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $skus, string $destinationSource, bool $defaultSourceOnly = false): ValidationResult
    {
        $errors = [];

        if (($destinationSource === $this->defaultSourceProvider->getCode()) && $defaultSourceOnly) {
            $errors[] = __('Cannot transfer default source to itself');
        }

        try {
            $this->sourceRepository->get($destinationSource);
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Source %sourceCode does not exist', ['sourceCode' => $destinationSource]);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
