<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogImportExport\Model\Import\Product\Validator;

use Magento\Framework\Validator\AbstractValidator;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\CatalogImportExport\Model\Import\Product;

class Category extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor
     */
    protected $categoryProcessor;

    /**
     * @param Product\CategoryProcessor $categoryProcessor
     */
    public function __construct(
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor
    ) {
        $this->categoryProcessor = $categoryProcessor;
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
        $emptyCategory = empty($value[Product::COL_CATEGORY]);
        $emptyRootCategory = empty($value[Product::COL_ROOT_CATEGORY]);
        $hasCategory = $emptyCategory
            ? false
            : !is_null($this->categoryProcessor->getCategory($value[Product::COL_CATEGORY]));
        $category = $emptyRootCategory
            ? null
            : $this->categoryProcessor->getCategoryWithRoot($value[Product::COL_ROOT_CATEGORY]);
        if (!$emptyCategory && !$hasCategory || !$emptyRootCategory && !isset(
                $category
            ) || !$emptyRootCategory && !$emptyCategory && !isset(
                $category[$value[Product::COL_CATEGORY]]
            )
        ) {
            $this->_addMessages([self::ERROR_INVALID_CATEGORY]);
            return false;
        }
        return true;
    }
}
