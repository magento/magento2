<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

/**
 * @api
 * @since 2.0.0
 */
class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product
     * @since 2.0.0
     */
    protected $_ruleConditionProd;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_ruleConditionProd = $ruleConditionProduct;
        $this->setType(\Magento\SalesRule\Model\Rule\Condition\Product\Combine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     * @since 2.0.0
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_ruleConditionProd->loadAttributeOptions()->getAttributeOption();
        $pAttributes = [];
        $iAttributes = [];
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = [
                    'value' => \Magento\SalesRule\Model\Rule\Condition\Product::class . '|' . $code,
                    'label' => $label,
                ];
            } else {
                $pAttributes[] = [
                    'value' => \Magento\SalesRule\Model\Rule\Condition\Product::class . '|' . $code,
                    'label' => $label,
                ];
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => \Magento\SalesRule\Model\Rule\Condition\Product\Combine::class,
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Cart Item Attribute'), 'value' => $iAttributes],
                ['label' => __('Product Attribute'), 'value' => $pAttributes]
            ]
        );
        return $conditions;
    }

    /**
     * Collect validated attributes
     *
     * @param Collection $productCollection
     * @return $this
     * @since 2.0.0
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
