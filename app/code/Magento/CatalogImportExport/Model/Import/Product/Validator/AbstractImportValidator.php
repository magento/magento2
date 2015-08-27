<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\Framework\Exception\State\InitException;
use Magento\Framework\Validator\AbstractValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\ContextInterface;

abstract class AbstractImportValidator extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $context;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        return $this;
    }

    /**
     * Set context object
     *
     * @param ContextInterface $context
     *
     * @return $this
     */
    public function setContext(ContextInterface $context)
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Get context object
     *
     * @return ContextInterface
     * @throws InitException
     */
    public function getContext()
    {
        if (!$this->context instanceof ContextInterface) {
            throw new InitException(__('Validation context is not initialized.'));
        }
        return $this->context;
    }
}