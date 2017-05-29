<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;

class Website extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver
     */
    protected $storeResolver;

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver
     */
    public function __construct(\Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver)
    {
        $this->storeResolver = $storeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (empty($value[ImportProduct::COL_PRODUCT_WEBSITES])) {
            return true;
        }
        $separator = $this->context->getMultipleValueSeparator();
        $websites = explode($separator, $value[ImportProduct::COL_PRODUCT_WEBSITES]);
        foreach ($websites as $website) {
            if (!$this->storeResolver->getWebsiteCodeToId($website)) {
                $this->_addMessages([self::ERROR_INVALID_WEBSITE]);
                return false;
            }
        }
        return true;
    }
}
