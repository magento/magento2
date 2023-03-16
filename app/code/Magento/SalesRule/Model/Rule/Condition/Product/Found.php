<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Context as ConditionContext;
use Magento\SalesRule\Model\Rule\Condition\Product as RuleCondProduct;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as RuleCondProductCombine;
use Magento\SalesRule\Model\Rule\Condition\Product\Found as RuleCondProductFound;

class Found extends RuleCondProductCombine
{
    /**
     * @param ConditionContext $context
     * @param RuleCondProduct $ruleConditionProduct
     * @param array $data
     */
    public function __construct(
        ConditionContext $context,
        RuleCondProduct $ruleConditionProduct,
        array $data = []
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType(RuleCondProductFound::class);
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption([1 => __('FOUND'), 0 => __('NOT FOUND')]);
        return $this;
    }

    /**
     * Return as html
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . __(
            "If an item is %1 in the cart with %2 of these conditions true:",
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
     * @param AbstractModel $model
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validate(AbstractModel $model)
    {
        $isValid = false;

        foreach ($model->getAllItems() as $item) {
            if (parent::validate($item)) {
                $isValid = true;
                break;
            }
        }

        return $isValid;
    }
}
