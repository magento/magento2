<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Converter;

use Magento\SalesRule\Model\Data\Condition;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\Data\Rule as RuleDataModel;
use Magento\SalesRule\Model\Rule;

class ToDataModel
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\SalesRule\Api\Data\RuleInterfaceFactory
     */
    protected $ruleDataFactory;

    /**
     * @var \Magento\SalesRule\Api\Data\ConditionInterfaceFactory
     */
    protected $conditionDataFactory;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\SalesRule\Api\Data\RuleLabelInterfaceFactory
     */
    protected $ruleLabelFactory;

    /**
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Api\Data\RuleInterfaceFactory $ruleDataFactory
     * @param \Magento\SalesRule\Api\Data\ConditionInterfaceFactory $conditionDataFactory
     * @param \Magento\SalesRule\Api\Data\RuleLabelInterfaceFactory $ruleLabelFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Api\Data\RuleInterfaceFactory $ruleDataFactory,
        \Magento\SalesRule\Api\Data\ConditionInterfaceFactory $conditionDataFactory,
        \Magento\SalesRule\Api\Data\RuleLabelInterfaceFactory $ruleLabelFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->ruleDataFactory = $ruleDataFactory;
        $this->conditionDataFactory = $conditionDataFactory;
        $this->ruleLabelFactory = $ruleLabelFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * @param Rule $ruleModel
     * @return RuleDataModel
     */
    public function toDataModel(\Magento\SalesRule\Model\Rule $ruleModel)
    {
        /** @var \Magento\SalesRule\Model\Data\Rule $dataModel */
        $dataModel = $this->ruleDataFactory->create(['data' => $ruleModel->getData()]);

        $this->mapFields($dataModel, $ruleModel);

        return $dataModel;
    }

    /**
     * @param RuleDataModel $dataModel
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @return $this
     */
    protected function mapConditions(RuleDataModel $dataModel, \Magento\SalesRule\Model\Rule $ruleModel)
    {
        $conditionSerialized = $ruleModel->getConditionsSerialized();
        if ($conditionSerialized) {
            $conditionArray = unserialize($conditionSerialized);
            $conditionDataModel = $this->arrayToConditionDataModel($conditionArray);
            $dataModel->setCondition($conditionDataModel);
        } else {
            $dataModel->setCondition(null);
        }
        return $this;
    }

    /**
     * @param RuleDataModel $dataModel
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @return $this
     */
    protected function mapActionConditions(RuleDataModel $dataModel, \Magento\SalesRule\Model\Rule $ruleModel)
    {
        $actionConditionSerialized = $ruleModel->getActionsSerialized();
        if ($actionConditionSerialized) {
            $actionConditionArray = unserialize($actionConditionSerialized);
            $actionConditionDataModel = $this->arrayToConditionDataModel($actionConditionArray);
            $dataModel->setActionCondition($actionConditionDataModel);
        } else {
            $dataModel->setActionCondition(null);
        }
        return $this;
    }

    /**
     * @param RuleDataModel $dataModel
     * @return $this
     */
    protected function mapStoreLabels(RuleDataModel $dataModel)
    {
        //translate store labels into objects
        if ($dataModel->getStoreLabels() !== null) {
            $storeLabels = [];
            foreach ($dataModel->getStoreLabels() as $storeId => $storeLabel) {
                $storeLabelObj = $this->ruleLabelFactory->create();
                $storeLabelObj->setStoreId($storeId);
                $storeLabelObj->setStoreLabel($storeLabel);
                $storeLabels[] = $storeLabelObj;
            }
            $dataModel->setStoreLabels($storeLabels);
        }
        return $this;
    }

    /**
     * @param RuleDataModel $dataModel
     * @return $this
     */
    protected function mapCouponType(RuleDataModel $dataModel)
    {
        if ($dataModel->getCouponType()) {
            $mappedValue = '';
            switch ((int)$dataModel->getCouponType()) {
                case \Magento\SalesRule\Model\Rule::COUPON_TYPE_NO_COUPON:
                    $mappedValue = RuleInterface::COUPON_TYPE_NO_COUPON;
                    break;
                case \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC:
                    $mappedValue = RuleInterface::COUPON_TYPE_SPECIFIC_COUPON;
                    break;
                case \Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO:
                    $mappedValue = RuleInterface::COUPON_TYPE_AUTO;
                    break;
                default:
            }
            $dataModel->setCouponType($mappedValue);
        }
        return $this;
    }

    /**
     * @param RuleDataModel $dataModel
     * @param \Magento\SalesRule\Model\Rule $ruleModel
     * @return $this
     */
    protected function mapFields(RuleDataModel $dataModel, \Magento\SalesRule\Model\Rule $ruleModel)
    {
        $this->mapConditions($dataModel, $ruleModel);
        $this->mapActionConditions($dataModel, $ruleModel);
        $this->mapStoreLabels($dataModel);
        $this->mapCouponType($dataModel);
        return $this;
    }

    /**
     * Convert recursive array into condition data model
     *
     * @param array $input
     * @return Condition
     */
    public function arrayToConditionDataModel(array $input)
    {
        /** @var \Magento\SalesRule\Model\Data\Condition $conditionDataModel */
        $conditionDataModel = $this->conditionDataFactory->create();
        foreach ($input as $key => $value) {
            switch ($key) {
                case 'type':
                    $conditionDataModel->setConditionType($value);
                    break;
                case 'attribute':
                    $conditionDataModel->setAttributeName($value);
                    break;
                case 'operator':
                    $conditionDataModel->setOperator($value);
                    break;
                case 'value':
                    $conditionDataModel->setValue($value);
                    break;
                case 'aggregator':
                    $conditionDataModel->setAggregatorType($value);
                    break;
                case 'conditions':
                    $conditions = [];
                    foreach ($value as $condition) {
                        $conditions[] = $this->arrayToConditionDataModel($condition);
                    }
                    $conditionDataModel->setConditions($conditions);
                    break;
                default:
            }
        }
        return $conditionDataModel;
    }
}
