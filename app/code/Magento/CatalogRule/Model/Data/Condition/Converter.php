<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\Data\Condition;

/**
 * Class \Magento\CatalogRule\Model\Data\Condition\Converter
 *
 */
class Converter
{
    /**
     * @var \Magento\CatalogRule\Api\Data\ConditionInterfaceFactory
     */
    protected $ruleConditionFactory;

    /**
     * @param \Magento\CatalogRule\Api\Data\ConditionInterfaceFactory $ruleConditionFactory
     */
    public function __construct(\Magento\CatalogRule\Api\Data\ConditionInterfaceFactory $ruleConditionFactory)
    {
        $this->ruleConditionFactory = $ruleConditionFactory;
    }

    /**
     * @param \Magento\CatalogRule\Api\Data\ConditionInterface $dataModel
     * @return array
     */
    public function dataModelToArray(\Magento\CatalogRule\Api\Data\ConditionInterface $dataModel)
    {
        $conditionArray = [
            'type' => $dataModel->getType(),
            'attribute' => $dataModel->getAttribute(),
            'operator' => $dataModel->getOperator(),
            'value' => $dataModel->getValue(),
            'is_value_processed' => $dataModel->getIsValueParsed(),
            'aggregator' => $dataModel->getAggregator()
        ];

        foreach ((array)$dataModel->getConditions() as $condition) {
            $conditionArray['conditions'][] = $this->dataModelToArray($condition);
        }

        return $conditionArray;
    }

    /**
     * @param array $conditionArray
     * @return \Magento\CatalogRule\Api\Data\ConditionInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function arrayToDataModel(array $conditionArray)
    {
        /** @var \Magento\CatalogRule\Api\Data\ConditionInterface $ruleCondition */
        $ruleCondition = $this->ruleConditionFactory->create();

        $ruleCondition->setType($conditionArray['type']);
        $ruleCondition->setAggregator(isset($conditionArray['aggregator']) ? $conditionArray['aggregator'] : false);
        $ruleCondition->setAttribute(isset($conditionArray['attribute']) ? $conditionArray['attribute'] : false);
        $ruleCondition->setOperator(isset($conditionArray['operator']) ? $conditionArray['operator'] : false);
        $ruleCondition->setValue(isset($conditionArray['value']) ? $conditionArray['value'] : false);
        $ruleCondition->setIsValueParsed(
            isset($conditionArray['is_value_parsed']) ? $conditionArray['is_value_parsed'] : false
        );

        if (isset($conditionArray['conditions']) && is_array($conditionArray['conditions'])) {
            $conditions = [];
            foreach ($conditionArray['conditions'] as $condition) {
                $conditions[] = $this->arrayToDataModel($condition);
            }
            $ruleCondition->setConditions($conditions);
        }
        return $ruleCondition;
    }
}
