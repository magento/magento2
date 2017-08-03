<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Tax Class
 */
namespace Magento\Tax\Model\TaxClass\Type;

/**
 * Class \Magento\Tax\Model\TaxClass\Type\Product
 *
 * @since 2.0.0
 */
class Product extends \Magento\Tax\Model\TaxClass\AbstractType
{
    /**
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $_modelProduct;

    /**
     * Class Type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_classType = \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT;

    /**
     * @param \Magento\Tax\Model\Calculation\Rule $calculationRule
     * @param \Magento\Catalog\Model\Product $modelProduct
     * @param array $data
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isAssignedToObjects()
    {
        return $this->_modelProduct->getCollection()->addAttributeToFilter('tax_class_id', $this->getId())
            ->getSize() > 0;
    }

    /**
     * Get Name of Objects that use this Tax Class Type
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getObjectTypeName()
    {
        return __('product');
    }
}
