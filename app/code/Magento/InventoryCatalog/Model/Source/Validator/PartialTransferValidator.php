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

class PartialTransferValidator implements PartialInventoryTransferValidatorInterface
{
    /** @var ValidationResultFactory */
    private $validationResultFactory;

    /** @var SourceRepositoryInterface */
    private $sourceRepository;

    /** @var GetSourceItemsBySkuAndSourceCodes  */
    private $getSourceItem;

    /**
     * PartialTransferValidator constructor.
     * @param ValidationResultFactory $validationResultFactory
     * @param SourceRepositoryInterface $sourceRepository
     * @param GetSourceItemsBySkuAndSourceCodes $getSourceItemsBySkuAndSourceCodes
     */
    public function __construct(
        ValidationResultFactory $validationResultFactory,
        SourceRepositoryInterface $sourceRepository,
        GetSourceItemsBySkuAndSourceCodes $getSourceItemsBySkuAndSourceCodes
    )
    {
        $this->validationResultFactory  = $validationResultFactory;
        $this->getSourceItem            = $getSourceItemsBySkuAndSourceCodes;
        $this->sourceRepository         = $sourceRepository;
    }

    /**
     * Validates a partial transfer request.
     *
     * @param PartialInventoryTransferInterface $item
     * @return ValidationResult
     */
    public function validate(PartialInventoryTransferInterface $item): ValidationResult
    {
        $errors = [];

        try {
            $this->sourceRepository->get($item->getOriginSourceCode());
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Origin source %sourceCode does not exist', ['sourceCode' => $item->getOriginSourceCode()]);
        }

        try {
            $this->sourceRepository->get($item->getDestinationSourceCode());
        } catch (NoSuchEntityException $e) {
            $errors[] = __('Destination source %sourceCode does not exist', ['sourceCode' => $item->getDestinationSourceCode()]);
        }

        if ($item->getOriginSourceCode() === $item->getDestinationSourceCode()) {
            $errors[] = __('Cannot transfer a source on itself');
        }

        try {
            $originSourceItem = $this->getSourceItemBySkuAndSource($item->getSku(), $item->getOriginSourceCode());
            if ($originSourceItem->getQuantity() < $item->getQty()) {
                $errors[] = __('Requested transfer amount for sku %sku is not available', $item->getSku());
            }

            $this->getSourceItemBySkuAndSource($item->getSku(), $item->getOriginSourceCode());
        } catch (NoSuchEntityException $e) {
            $errors[] = $e->getMessage();
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