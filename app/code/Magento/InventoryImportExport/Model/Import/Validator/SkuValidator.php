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
        }

        return $this->validationResultFactory->create(['errors' => $errors]);
    }
}
