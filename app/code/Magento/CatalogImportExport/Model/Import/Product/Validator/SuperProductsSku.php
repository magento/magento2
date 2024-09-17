<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;

class SuperProductsSku extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var SkuProcessor
     */
    protected $skuProcessor;

    /**
     * @var SkuStorage
     */
    private SkuStorage $skuStorage;

    /**
     * @param SkuProcessor $skuProcessor
     * @param SkuStorage $skuStorage
     */
    public function __construct(
        SkuProcessor $skuProcessor,
        SkuStorage $skuStorage
    ) {
        $this->skuProcessor = $skuProcessor;
        $this->skuStorage = $skuStorage;
    }

    /**
     * Validates super product sku to exist in db or in the import
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!empty($value['_super_products_sku'])) {
            if (!$this->skuStorage->has($value['_super_products_sku'])
                && $this->skuProcessor->getNewSku($value['_super_products_sku']) === null
            ) {
                $this->_addMessages([self::ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND]);
                return false;
            }
        }
        return true;
    }
}
