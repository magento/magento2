<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class Quantity extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!empty($value['qty']) && (!is_numeric($value['qty'])
            && $value['qty'] !== $this->context->getEmptyAttributeValueConstant())
        ) {
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(self::ERROR_INVALID_ATTRIBUTE_TYPE),
                        'qty',
                        'decimal'
                    ),
                ]
            );
            return false;
        }
        return true;
    }
}
