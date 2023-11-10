<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;

class Found extends \Magento\SalesRule\Model\Rule\Condition\Product\Combine
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
        $this->setType(\Magento\SalesRule\Model\Rule\Condition\Product\Found::class);
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
        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();

        foreach ($model->getAllItems() as $item) {
            $validated = parent::validate($item);
            if (!$true && !$validated) {
                $isValid = false;
                break;
            }
            if (!$all && $validated) {
                $isValid = true;
                break;
            }
            if ($all && $true && $validated) {
                $isValid = true;
                break;
            }
            if ($all && !$true && $validated) {
                $isValid = true;
            }
        }
        return $isValid;
    }
}
