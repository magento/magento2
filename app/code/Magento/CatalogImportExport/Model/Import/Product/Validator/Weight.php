<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class Weight extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!empty($value['weight']) && (!is_numeric($value['weight']) || $value['weight'] < 0)
            && $value['weight'] !== $this->context->getEmptyAttributeValueConstant()
        ) {
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(self::ERROR_INVALID_ATTRIBUTE_TYPE),
                        'weight',
                        'decimal'
                    )
                ]
            );
            return false;
        }
        return true;
    }
}
