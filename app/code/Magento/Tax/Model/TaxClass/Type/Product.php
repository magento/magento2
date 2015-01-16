<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Tax Class
 */
namespace Magento\Tax\Model\TaxClass\Type;

class Product extends \Magento\Tax\Model\TaxClass\AbstractType
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_modelProduct;

    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT;

    /**
     * @param \Magento\Tax\Model\Calculation\Rule $calculationRule
     * @param \Magento\Catalog\Model\Product $modelProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Model\Calculation\Rule $calculationRule,
        \Magento\Catalog\Model\Product $modelProduct,
        array $data = []
    ) {
        parent::__construct($calculationRule, $data);
        $this->_modelProduct = $modelProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function isAssignedToObjects()
    {
        return $this->_modelProduct->getCollection()->addAttributeToFilter('tax_class_id', $this->getId())
            ->getSize() > 0;
    }

    /**
     * Get Name of Objects that use this Tax Class Type
     *
     * @return string
     */
    public function getObjectTypeName()
    {
        return __('product');
    }
}
