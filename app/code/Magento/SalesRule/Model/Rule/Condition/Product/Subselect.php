<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\SalesRule\Model\Rule\Condition\Product;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\Data\TotalsItemInterface;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product;

/**
 * SubSelect conditions for product.
 */
class Subselect extends Combine
{
    /**
     * @param Context $context
     * @param Product $ruleConditionProduct
     * @param array $data
     */
    public function __construct(
        Context $context,
        Product $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType(Subselect::class)->setValue(null);
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
        $this->setAttributeOption(
            [
                'qty' => __('total quantity'),
                'base_row_total' => __('total amount (excl. tax)'),
                'base_row_total_incl_tax' => __('total amount (incl. tax)')
            ]
        );
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
     * Validate subSelect conditions, base_row_total and attribute
     *
     * @param AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(AbstractModel $model)
    {
        $subSelectConditionsFlag = true;
        if (!$this->getConditions()) {
            return false;
        }
        $attr = $this->getAttribute();
        $total = 0;
        $isMultiShipping = (bool) $model->getQuote()->getIsMultiShipping();
        $items = $isMultiShipping ? $model->getAllItems() : $model->getQuote()->getAllVisibleItems();
        foreach ($items as $item) {
            if ($isMultiShipping) {
                $subSelectConditionsFlag = $this->validateSubSelectConditions($item);
            }
            $total = $this->getBaseRowTotalForChildrenProduct($item, $attr, $total);
        }
        return $subSelectConditionsFlag && $this->validateAttribute($total);
    }

    /**
     * Check subSelect conditions to verify if they are met
     *
     * @param mixed $item
     * @return bool
     */
    private function validateSubSelectConditions(mixed $item): bool
    {
        $subSelectConditionsFlag = true;
        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();
        $conditions = $this->getConditions();
        if (!empty($conditions)) {
            foreach ($conditions as $cond) {
                if ($item instanceof AbstractModel) {
                    $validated = $cond->validate($item);
                } else {
                    $validated = $cond->validateByEntityId($item);
                }
                if ($all && $validated !== $true) {
                    $subSelectConditionsFlag = false;
                    break;
                } elseif (!$all && $validated === $true) {
                    continue;
                }
            }
        }
        return $subSelectConditionsFlag;
    }

    /**
     * Get base row total for children product for bundle and configurable product
     *
     * @param mixed $item
     * @param mixed $attr
     * @param int $total
     * @return int|mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getBaseRowTotalForChildrenProduct(mixed $item, mixed $attr, int $total): mixed
    {
        $hasValidChild = false;
        $useChildrenTotal = ($item->getProductType() == Type::TYPE_BUNDLE);
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
        return $total;
    }
}
