<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Observer;

class CheckSalesRulesAvailability
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->messageManager = $messageManager;
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
        /* @var $collection \Magento\SalesRule\Model\ResourceModel\Rule\Collection */
        $collection = $this->_collectionFactory->create()->addAttributeInConditionFilter($attributeCode);

        $disabledRulesCount = 0;
        foreach ($collection as $rule) {
            /* @var $rule \Magento\SalesRule\Model\Rule */
            $rule->setIsActive(0);
            /* @var $rule->getConditions() \Magento\SalesRule\Model\Rule\Condition\Combine */
            $this->_removeAttributeFromConditions($rule->getConditions(), $attributeCode);
            $this->_removeAttributeFromConditions($rule->getActions(), $attributeCode);
            $rule->save();

            $disabledRulesCount++;
        }

        if ($disabledRulesCount) {
            $this->messageManager->addWarning(
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
     * @param \Magento\Rule\Model\Condition\Combine $combine
     * @param string $attributeCode
     * @return void
     */
    protected function _removeAttributeFromConditions($combine, $attributeCode)
    {
        $conditions = $combine->getConditions();
        foreach ($conditions as $conditionId => $condition) {
            if ($condition instanceof \Magento\Rule\Model\Condition\Combine) {
                $this->_removeAttributeFromConditions($condition, $attributeCode);
            }
            if ($condition instanceof \Magento\SalesRule\Model\Rule\Condition\Product) {
                if ($condition->getAttribute() == $attributeCode) {
                    unset($conditions[$conditionId]);
                }
            }
        }
        $combine->setConditions($conditions);
    }
}
