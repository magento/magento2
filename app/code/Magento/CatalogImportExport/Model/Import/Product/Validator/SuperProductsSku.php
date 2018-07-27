<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class SuperProductsSku extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor
     */
    protected $skuProcessor;

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor
     */
    public function __construct(
        \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor
    ) {
        $this->skuProcessor = $skuProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function init($context)
    {
        return parent::init($context);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        $oldSku = $this->skuProcessor->getOldSkus();
        if (!empty($value['_super_products_sku']) && (!isset(
                $oldSku[$value['_super_products_sku']]
            ) && $this->skuProcessor->getNewSku($value['_super_products_sku']) === null
            )
        ) {
            $this->_addMessages([self::ERROR_SUPER_PRODUCTS_SKU_NOT_FOUND]);
            return false;
        }
        return true;

    }
}
