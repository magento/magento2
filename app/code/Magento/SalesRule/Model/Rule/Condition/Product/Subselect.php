<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition\Product;

use Magento\Quote\Api\Data\TotalsItemInterface;

/**
 * Subselect conditions for product.
 */
class Subselect extends \Magento\SalesRule\Model\Rule\Condition\Product\Combine
{
    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\Product $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType(\Magento\SalesRule\Model\Rule\Condition\Product\Subselect::class)->setValue(null);
    }

    /**
     * Load array
     *
     * @param array $arr
     * @param string $key
     * @return $this
     */
    public function loadArray($arr, $key = 'conditions')
    {
        $this->setAttribute($arr['attribute']);
        $this->setOperator($arr['operator']);
        parent::loadArray($arr, $key);
        return $this;
    }

    /**
     * Return as xml
     *
     * @param string $containerKey
     * @param string $itemKey
     * @return string
     */
    public function asXml($containerKey = 'conditions', $itemKey = 'condition')
    {
        $xml = '<attribute>' .
            $this->getAttribute() .
            '</attribute>' .
            '<operator>' .
            $this->getOperator() .
            '</operator>' .
            parent::asXml(
                $containerKey,
                $itemKey
            );
        return $xml;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(['qty' => __('total quantity'), 'base_row_total' => __('total amount')]);
        return $this;
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        return $this;
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(
            [
                '==' => __('is'),
                '!=' => __('is not'),
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
                '()' => __('is one of'),
                '!()' => __('is not one of'),
            ]
        );
        return $this;
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'text';
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
            "If %1 %2 %3 for a subselection of items in cart matching %4 of these conditions:",
            $this->getAttributeElement()->getHtml(),
            $this->getOperatorElement()->getHtml(),
            $this->getValueElement()->getHtml(),
            $this->getAggregatorElement()->getHtml()
        );
        if ($this->getId() != '1') {
            $html .= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * Validate
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if (!$this->getConditions()) {
            return false;
        }
        $attr = $this->getAttribute();
        $total = 0;
        foreach ($model->getQuote()->getAllVisibleItems() as $item) {
            $hasValidChild = false;
            $useChildrenTotal = ($item->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);
            $childrenAttrTotal = 0;
            $children = $item->getChildren();
            if (!empty($children)) {
                foreach ($children as $child) {
                    if (parent::validate($child)) {
                        $hasValidChild = true;
                        if ($useChildrenTotal) {
                            $childrenAttrTotal += $child->getData($attr);
                        }
                    }
                }
            }
            if ($attr !== TotalsItemInterface::KEY_BASE_ROW_TOTAL) {
                $childrenAttrTotal *= $item->getQty();
            }
            if ($hasValidChild || parent::validate($item)) {
                $total += ($hasValidChild && $useChildrenTotal && $childrenAttrTotal > 0)
                    ? $childrenAttrTotal
                    : $item->getData($attr);
            }
        }
        return $this->validateAttribute($total);
    }
}
