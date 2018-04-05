<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\StockSourceLink\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Inventory\Model\StockSourceLink\Validator\StockSourceLinkValidatorInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

class AssignNonDefaultSourceToDefaultStockValidator implements StockSourceLinkValidatorInterface
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
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * AssignNonDefaultSourceToDefaultStockValidator constructor.
     * @param ValidationResultFactory $validationResultFactory
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param DefaultStockProviderInterface $defaultStockProvider
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        DefaultSourceProviderInterface $defaultSourceProvider,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->validationResultFactory = $validationResultFactory;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @inheritdoc
     */
    public function validate(StockSourceLinkInterface $link): ValidationResult
    {
        $errors = [];

        if ($link->getStockId() === $this->defaultStockProvider->getId() &&
            $link->getSourceCode() !== $this->defaultSourceProvider->getCode()
        ) {
            $errors[] = __('Only Default Source can be assigned to Default Stock');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
