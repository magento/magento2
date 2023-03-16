<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Observer;

use Magento\Framework\Message\ManagerInterface;
use Magento\Rule\Model\Condition\Combine as ConditionCombine;
use Magento\SalesRule\Model\ResourceModel\Rule\Collection as RuleCollection;
use Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Magento\SalesRule\Model\Rule as ModelRule;
use Magento\SalesRule\Model\Rule\Condition\Product as ConditionProduct;

class CheckSalesRulesAvailability
{
    /**
     * @var RuleCollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param RuleCollectionFactory $collectionFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RuleCollectionFactory $collectionFactory,
        protected readonly ManagerInterface $messageManager
    ) {
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Check rules that contains affected attribute
     * If rules were found they will be set to inactive and notice will be add to admin session
     *
     * @param string $attributeCode
     * @return $this
     */
    public function checkSalesRulesAvailability($attributeCode)
    {
        /* @var RuleCollection $collection */
        $collection = $this->_collectionFactory->create()->addAttributeInConditionFilter($attributeCode);

        $disabledRulesCount = 0;
        foreach ($collection as $rule) {
            /** @var ModelRule $rule */
            $rule->setIsActive(0);
            /** @var ConditionCombine $rule->getConditions() */
            $this->_removeAttributeFromConditions($rule->getConditions(), $attributeCode);
            $this->_removeAttributeFromConditions($rule->getActions(), $attributeCode);
            $rule->save();

            $disabledRulesCount++;
        }

        if ($disabledRulesCount) {
            $this->messageManager->addWarningMessage(
                __(
                    '%1 Cart Price Rules based on "%2" attribute have been disabled.',
                    $disabledRulesCount,
                    $attributeCode
                )
            );
        }

        return $this;
    }

    /**
     * Remove catalog attribute condition by attribute code from rule conditions
     *
     * @param ConditionCombine $combine
     * @param string $attributeCode
     * @return void
     */
    protected function _removeAttributeFromConditions($combine, $attributeCode)
    {
        $conditions = $combine->getConditions();
        foreach ($conditions as $conditionId => $condition) {
            if ($condition instanceof ConditionCombine) {
                $this->_removeAttributeFromConditions($condition, $attributeCode);
            }
            if ($condition instanceof ConditionProduct) {
                if ($condition->getAttribute() == $attributeCode) {
                    unset($conditions[$conditionId]);
                }
            }
        }
        $combine->setConditions($conditions);
    }
}
