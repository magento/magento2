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
namespace Magento\SalesRule\Model\Rule\Condition;

class Combine extends \Magento\Rule\Model\Condition\Combine
{
    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\Address
     */
    protected $_conditionAddress;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress
     * @param array $data
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\SalesRule\Model\Rule\Condition\Address $conditionAddress,
        array $data = array()
    ) {
        $this->_eventManager = $eventManager;
        $this->_conditionAddress = $conditionAddress;
        parent::__construct($context, $data);
        $this->setType('Magento\SalesRule\Model\Rule\Condition\Combine');
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $addressAttributes = $this->_conditionAddress->loadAttributeOptions()->getAttributeOption();
        $attributes = array();
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = array(
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Address|' . $code,
                'label' => $label
            );
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            array(
                array(
                    'value' => 'Magento\SalesRule\Model\Rule\Condition\Product\Found',
                    'label' => __('Product attribute combination')
                ),
                array(
                    'value' => 'Magento\SalesRule\Model\Rule\Condition\Product\Subselect',
                    'label' => __('Products subselection')
                ),
                array(
                    'value' => 'Magento\SalesRule\Model\Rule\Condition\Combine',
                    'label' => __('Conditions combination')
                ),
                array('label' => __('Cart Attribute'), 'value' => $attributes)
            )
        );

        $additional = new \Magento\Framework\Object();
        $this->_eventManager->dispatch('salesrule_rule_condition_combine', array('additional' => $additional));
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}
