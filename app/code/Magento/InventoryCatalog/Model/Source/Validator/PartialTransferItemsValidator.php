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
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Model\GetSourceItemsBySkuAndSourceCodes;
use Magento\InventoryCatalogApi\Model\PartialInventoryTransferValidatorInterface;

class PartialTransferItemsValidator implements PartialInventoryTransferValidatorInterface
{
    /** @var ValidationResultFactory */
    private $validationResultFactory;

    /** @var GetSourceItemsBySkuAndSourceCodes  */
    private $getSourceItem;

    /**
     * @param ValidationResultFactory $validationResultFactory
     * @param GetSourceItemsBySkuAndSourceCodes $getSourceItemsBySkuAndSourceCodes
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        GetSourceItemsBySkuAndSourceCodes $getSourceItemsBySkuAndSourceCodes
    ) {
        $this->validationResultFactory  = $validationResultFactory;
        $this->getSourceItem            = $getSourceItemsBySkuAndSourceCodes;
    }

    /**
     * @inheritdoc
     */
    public function validate(string $originSourceCode, string $destinationSourceCode, array $items): ValidationResult
    {
        $errors = [];

        foreach ($items as $item) {
            try {
                $originSourceItem = $this->getSourceItemBySkuAndSource($item->getSku(), $originSourceCode);
                if ($originSourceItem->getQuantity() < $item->getQty()) {
                    $errors[] = __('Requested transfer amount for sku %sku is not available', ['sku' => $item->getSku()]);
                }

                $this->getSourceItemBySkuAndSource($item->getSku(), $destinationSourceCode);
            } catch (NoSuchEntityException $e) {
                $errors[] = __('%message', ['message' => $e->getMessage()]);
            }
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }

    /**
     * @param string $sku
     * @param string $sourceCode
     * @return SourceItemInterface
     * @throws NoSuchEntityException
     */
    private function getSourceItemBySkuAndSource(string $sku, string $sourceCode): SourceItemInterface
    {
        $result = $this->getSourceItem->execute($sku, [$sourceCode]);
        if (!count($result)) {
            throw new NoSuchEntityException(__('Source item for %sku and %sourceCode does not exist', ['sku' => $sku, 'sourceCode' => $sourceCode]));
        }

        return array_shift($result);
    }
}