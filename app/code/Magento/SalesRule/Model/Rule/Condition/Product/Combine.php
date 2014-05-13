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

use Magento\Catalog\Model\Resource\Product\Collection;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Product
     */
    protected $_ruleConditionProd;

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
        parent::__construct($context, $data);
        $this->_ruleConditionProd = $ruleConditionProduct;
        $this->setType('Magento\SalesRule\Model\Rule\Condition\Product\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_ruleConditionProd->loadAttributeOptions()->getAttributeOption();
        $pAttributes = array();
        $iAttributes = array();
        foreach ($productAttributes as $code => $label) {
            if (strpos($code, 'quote_item_') === 0) {
                $iAttributes[] = array(
                    'value' => 'Magento\SalesRule\Model\Rule\Condition\Product|' . $code,
                    'label' => $label
                );
            } else {
                $pAttributes[] = array(
                    'value' => 'Magento\SalesRule\Model\Rule\Condition\Product|' . $code,
                    'label' => $label
                );
            }
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                array(
                    'value' => 'Magento\SalesRule\Model\Rule\Condition\Product\Combine',
                    'label' => __('Conditions Combination')
                ),
                array('label' => __('Cart Item Attribute'), 'value' => $iAttributes),
                array('label' => __('Product Attribute'), 'value' => $pAttributes)
            )
        );
        return $conditions;
    }

    /**
     * Collect validated attributes
     *
     * @param Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
