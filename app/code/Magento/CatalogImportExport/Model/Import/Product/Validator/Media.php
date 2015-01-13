<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use \Magento\Framework\Validator\AbstractValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class Media extends AbstractValidator implements RowValidatorInterface
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
        if (!empty($value['_media_image']) && empty($value['_media_attribute_id'])) {
            $this->_addMessages([self::ERROR_MEDIA_DATA_INCOMPLETE]);
            return false;
        }
        return true;
    }
}
