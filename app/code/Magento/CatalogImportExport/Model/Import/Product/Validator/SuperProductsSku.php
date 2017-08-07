<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;

/**
 * Class \Magento\CatalogImportExport\Model\Import\Product\Validator\SuperProductsSku
 *
 */
class SuperProductsSku extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var SkuProcessor
     */
    protected $skuProcessor;

    /**
     * @param SkuProcessor $skuProcessor
     */
    public function __construct(
        SkuProcessor $skuProcessor
    ) {
        $this->skuProcessor = $skuProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $oldSku = $this->skuProcessor->getOldSkus();
        if (!empty($value['_super_products_sku'])) {
            $superSku = strtolower($value['_super_products_sku']);
            if (!isset($oldSku[$superSku])
                && $this->skuProcessor->getNewSku($superSku) === null
            ) {
                $this->_addMessages([self::ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND]);
                return false;
            }
        }
        return true;
    }
}
