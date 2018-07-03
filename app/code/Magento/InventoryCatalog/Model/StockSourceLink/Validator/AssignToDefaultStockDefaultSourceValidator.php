<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model\StockSourceLink\Validator;

use Magento\Framework\Validation\ValidationResult;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryApi\Model\StockSourceLinkValidatorInterface;
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
        $initialAssignment = $this->isInitialAssignment($link);
        $linkContainDefaultSourceOrStock = $this->isLinkContainDefaultSourceOrStock($link);
        $errors = [];
        if (!$initialAssignment && $linkContainDefaultSourceOrStock) {
            $errors[] = __('Can not save link related to Default Source or Default Stock');
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }

    /**
     * Checks whether StockSourceLink represents assignment of Default Source on Default Stock
     *
     * @param StockSourceLinkInterface $link
     * @return bool
     */
    private function isInitialAssignment(StockSourceLinkInterface $link)
    {
        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultSourceCode = $this->defaultSourceProvider->getCode();
        $linkStockId = $link->getStockId();
        $linkSourceCode = $link->getSourceCode();
        $initialAssignment = false;
        if ($defaultStockId === $linkStockId && $defaultSourceCode === $linkSourceCode) {
            $initialAssignment = true;
        }
        return $initialAssignment;
    }

    /**
     * Checks whether StockSourceLink contains reference to Default Source or Default Stock,
     * which is currently forbidden (just initial assignment of Default Source on Default Stock allowed), as
     * Default Source -> Default Stock linkage used to represent Single Source Mode
     *
     * @param StockSourceLinkInterface $link
     * @return bool
     */
    private function isLinkContainDefaultSourceOrStock(StockSourceLinkInterface $link)
    {
        $defaultStockId = $this->defaultStockProvider->getId();
        $defaultSourceCode = $this->defaultSourceProvider->getCode();
        $linkStockId = $link->getStockId();
        $linkSourceCode = $link->getSourceCode();
        $linkContainDefaultSourceOrStock = false;
        if ($linkStockId === $defaultStockId || $linkSourceCode === $defaultSourceCode) {
            $linkContainDefaultSourceOrStock = true;
        }
        return $linkContainDefaultSourceOrStock;
    }
}
