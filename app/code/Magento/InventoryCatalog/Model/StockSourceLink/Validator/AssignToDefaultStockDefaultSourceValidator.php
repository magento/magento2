<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\StockSourceLink\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Api\StockSourceLinkValidatorInterface;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

class AssignToDefaultStockDefaultSourceValidator implements StockSourceLinkValidatorInterface
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
        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultSourceCode = $this->defaultSourceProvider->getCode();
        $linkStockId = $link->getStockId();
        $linkSourceCode = $link->getSourceCode();
        $initialAssign = false;
        if ($defaultStockId === $linkStockId
        && $defaultSourceCode === $linkSourceCode) {
            $initialAssign = true;
        }

        $errors = [];
        if (!$initialAssign && $linkStockId === $defaultStockId
        || $linkSourceCode === $defaultSourceCode) {
            $errors[] = __('Can not save link related to Default Source or Default Stock');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
