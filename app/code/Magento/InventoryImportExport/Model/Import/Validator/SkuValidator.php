<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryImportExport\Model\Import\Validator;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Validation\ValidationResultFactory;
use Magento\InventoryImportExport\Model\Import\Sources;

/**
 * Extension point for row validation
 */
class SkuValidator implements ValidatorInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param ValidationResultFactory $validationResultFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ValidationResultFactory $validationResultFactory
    ) {
        $this->collectionFactory = $collectionFactory;
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
     * Attempt to get Product collection filtered using SKU check size and return bool
     *
     * @param string $sku
     * @return bool
     */
    private function isValidSku(string $sku): bool
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToFilter(ProductInterface::SKU, $sku);
        return $collection->getSize() > 0;
    }
}
