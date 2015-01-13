<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use \Magento\Framework\Validator\AbstractValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class Weight extends AbstractValidator implements RowValidatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!empty($value['weight']) && (!is_numeric($value['weight']) || $value['weight'] < 0)) {
            $this->_addMessages([self::ERROR_INVALID_WEIGHT]);
            return false;
        }
        return true;
    }
}
