<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Validator;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryImportExport\Model\Import\Sources;

/**
 * Extension point for row validation
 */
class SkuValidator implements ValidatorInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ValidationResultFactory $validationResultFactory
    ) {
        $this->productRepository = $productRepository;
        $this->validationResultFactory = $validationResultFactory;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $rowData, int $rowNumber)
    {
        $errors = [];

        if (!isset($rowData[Sources::COL_SKU])) {
            $errors[] = __('Missing required column "%column"', ['column' => Sources::COL_SKU]);
        } elseif (!$this->isValidSku($rowData[Sources::COL_SKU])) {
            $errors[] = __('Product with SKU "%sku" does not exist', ['sku' => $rowData[Sources::COL_SKU]]);
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }

    /**
     * Attempt to get Product via Repository using SKU catch NoSuchEntityException
     * If caught product doesn't exist so return false otherwise return true
     *
     * @param $sku
     * @return bool
     */
    private function isValidSku($sku)
    {
        try {
            $this->productRepository->get($sku);
        } catch (NoSuchEntityException $e) {
            return false;
        }
        return true;
    }
}
