<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Rule\Condition;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Rule\Model\Condition\Combine as ConditionCombine;
use Magento\Rule\Model\Condition\Context as ConditionContext;
use Magento\SalesRule\Model\Rule\Condition\Address as RuleConditionAddress;
use Magento\SalesRule\Model\Rule\Condition\Combine as RuleConditionCombine;
use Magento\SalesRule\Model\Rule\Condition\Product\Found as RuleCondProductFound;
use Magento\SalesRule\Model\Rule\Condition\Product\Subselect as RuleCondProductSubselect;

/**
 * @api
 * @since 100.0.2
 */
class Combine extends ConditionCombine
{
    /**
     * Core event manager proxy
     *
     * @var EventManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var RuleConditionAddress
     */
    protected $_conditionAddress;

    /**
     * @param ConditionContext $context
     * @param EventManagerInterface $eventManager
     * @param RuleConditionAddress $conditionAddress
     * @param array $data
     */
    public function __construct(
        ConditionContext $context,
        EventManagerInterface $eventManager,
        RuleConditionAddress $conditionAddress,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_conditionAddress = $conditionAddress;
        parent::__construct($context, $data);
        $this->setType(RuleConditionCombine::class);
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $addressAttributes = $this->_conditionAddress->loadAttributeOptions()->getAttributeOption();
        $attributes = [];
        foreach ($addressAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Magento\SalesRule\Model\Rule\Condition\Address|' . $code,
                'label' => $label,
            ];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => RuleCondProductFound::class,
                    'label' => __('Product attribute combination'),
                ],
                [
                    'value' => RuleCondProductSubselect::class,
                    'label' => __('Products subselection')
                ],
                [
                    'value' => RuleConditionCombine::class,
                    'label' => __('Conditions combination')
                ],
                ['label' => __('Cart Attribute'), 'value' => $attributes]
            ]
        );

        $additional = new DataObject();
        $this->_eventManager->dispatch('salesrule_rule_condition_combine', ['additional' => $additional]);
        $additionalConditions = $additional->getConditions();
        if ($additionalConditions) {
            $conditions = array_merge_recursive($conditions, $additionalConditions);
        }

        return $conditions;
    }
}
