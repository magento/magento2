<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\Validator\AbstractImportValidator;

class Validator extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @var RowValidatorInterface[]|AbstractImportValidator[]
     */
    protected $validators = [];

    /**
     * @param RowValidatorInterface[] $validators
     */
    public function __construct($validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $returnValue = true;
        $this->_clearMessages();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value)) {
                $returnValue = false;
                $this->_addMessages($validator->getMessages());
            }
        }
        return $returnValue;
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        foreach ($this->validators as $validator) {
            $validator->setContext($this->getContext())->init();
        }
    }
}
