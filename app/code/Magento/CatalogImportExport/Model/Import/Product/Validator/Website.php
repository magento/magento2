<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use \Magento\Framework\Validator\AbstractValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;

class Website extends AbstractValidator implements RowValidatorInterface
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
        if (!empty($value['_product_websites'])
            && !$this->storeResolver->getWebsiteCodeToId($value['_product_websites'])
        ) {
            $this->_addMessages([self::ERROR_INVALID_WEBSITE]);
            return false;
        }
        return true;
    }
}
