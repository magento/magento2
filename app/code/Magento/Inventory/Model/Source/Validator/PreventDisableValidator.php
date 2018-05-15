<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Source\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryApi\Model\SourceValidatorInterface;

/**
 * Check that default source always enabled
 */
class PreventDisableValidator implements SourceValidatorInterface
{
    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        DefaultSourceProviderInterface $defaultSourceProvider
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->defaultSourceProvider = $defaultSourceProvider;
    }

    /**
     * @inheritdoc
     */
    public function validate(SourceInterface $source): ValidationResult
    {
        $sourceCode = (string)$source->getSourceCode();
        if ($sourceCode !== $this->defaultSourceProvider->getCode()) {
            return $this->validationResultFactory->create(['errors' => []]);
        }
        $value = (bool)$source->isEnabled();
        if (!$value) {
            $errors[] = __('Default source can not be disabled.');
        } else {
            $errors = [];
        }
        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
