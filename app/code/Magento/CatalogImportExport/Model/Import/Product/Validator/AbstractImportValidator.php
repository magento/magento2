<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

/**
 * Class \Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator
 *
 * @since 2.0.0
 */
abstract class AbstractImportValidator extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     * @since 2.0.0
     */
    protected $context;

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     * @since 2.0.0
     */
    public function init($context)
    {
        $this->context = $context;
        return $this;
    }
}
