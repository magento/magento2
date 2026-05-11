<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogInventory\Model\Source\Backorders as BackordersSource;

class Backorders extends AbstractImportValidator implements RowValidatorInterface
{
    /**
     * @param BackordersSource $backordersSource
     */
    public function __construct(
        private readonly BackordersSource $backordersSource
    ) {
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!isset($value['allow_backorders'])) {
            return true;
        }

        $backorders = $value['allow_backorders'];
        if (in_array($backorders, [$this->context->getEmptyAttributeValueConstant(), ''], true)) {
            return true;
        }

        if (!is_numeric($backorders)) {
            $messageTemplate = $this->context->retrieveMessageTemplate(self::ERROR_INVALID_ATTRIBUTE_TYPE);
            $message = sprintf($messageTemplate, 'allow_backorders');
            $this->_addMessages([$message]);
            return false;
        }

        $allowedValues = array_column($this->backordersSource->toOptionArray(), 'label', 'value');
        if (!isset($allowedValues[$backorders])) {
            $messageTemplate = $this->context->retrieveMessageTemplate(self::ERROR_INVALID_ATTRIBUTE_OPTION);
            $message = sprintf($messageTemplate, 'allow_backorders');
            $this->_addMessages([$message]);
            return false;
        }

        return true;
    }
}
