<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

/**
 * Class Quantity
 * @since 2.0.0
 */
class Quantity extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!empty($value['qty']) && !is_numeric($value['qty'])) {
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
