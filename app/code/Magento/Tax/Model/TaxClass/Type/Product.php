<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Tax Class
 */
namespace Magento\Tax\Model\TaxClass\Type;

use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Framework\Phrase;
use Magento\Tax\Model\Calculation\Rule as CalculationRule;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\TaxClass\AbstractType as TaxClassAbstractType;

class Product extends TaxClassAbstractType
{
    /**
     * @var ModelProduct
     */
    protected $_modelProduct;

    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType = TaxClassModel::TAX_CLASS_TYPE_PRODUCT;

    /**
     * @param CalculationRule $calculationRule
     * @param ModelProduct $modelProduct
     * @param array $data
     */
    public function __construct(
        CalculationRule $calculationRule,
        ModelProduct $modelProduct,
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
     * @return Phrase
     */
    public function getObjectTypeName()
    {
        return __('product');
    }
}
