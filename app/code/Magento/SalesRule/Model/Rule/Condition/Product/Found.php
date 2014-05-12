<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model\Rule\Condition\Product;

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
        array $data = array()
    ) {
        parent::__construct($context, $ruleConditionProduct, $data);
        $this->setType('Magento\SalesRule\Model\Rule\Condition\Product\Found');
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array(1 => __('FOUND'), 0 => __('NOT FOUND')));
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
     * @param \Magento\Framework\Object $object Quote
     * @return bool
     */
    public function validate(\Magento\Framework\Object $object)
    {
        $all = $this->getAggregator() === 'all';
        $true = (bool)$this->getValue();
        $found = false;
        foreach ($object->getAllItems() as $item) {
            $found = $all;
            foreach ($this->getConditions() as $cond) {
                $validated = $cond->validate($item);
                if ($all && !$validated || !$all && $validated) {
                    $found = $validated;
                    break;
                }
            }
            if ($found && $true || !$true && $found) {
                break;
            }
        }
        // found an item and we're looking for existing one
        if ($found && $true) {
            return true;
        } elseif (!$found && !$true) {
            // not found and we're making sure it doesn't exist
            return true;
        }
        return false;
    }
}
