<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Converter;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\SalesRule\Api\Data\ConditionInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleExtensionFactory;
use Magento\SalesRule\Api\Data\RuleExtensionInterface;
use Magento\SalesRule\Api\Data\RuleInterfaceFactory;
use Magento\SalesRule\Api\Data\RuleLabelInterfaceFactory;
use Magento\SalesRule\Model\Data\Condition;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\Data\Condition as ConditionDataModel;
use Magento\SalesRule\Model\Data\Rule as RuleDataModel;
use Magento\SalesRule\Model\Rule;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\SalesRule\Model\RuleFactory;

class ToDataModel
{
    /**
     * @param RuleFactory $ruleFactory
     * @param RuleInterfaceFactory $ruleDataFactory
     * @param ConditionInterfaceFactory $conditionDataFactory
     * @param RuleLabelInterfaceFactory $ruleLabelFactory
     * @param DataObjectProcessor $dataObjectProcessor
     * @param Json $serializer Optional parameter for backward compatibility
     * @param RuleExtensionFactory|null $extensionFactory
     */
    public function __construct(
        protected readonly RuleFactory $ruleFactory,
        protected readonly RuleInterfaceFactory $ruleDataFactory,
        protected readonly ConditionInterfaceFactory $conditionDataFactory,
        protected readonly RuleLabelInterfaceFactory $ruleLabelFactory,
        protected readonly DataObjectProcessor $dataObjectProcessor,
        private ?Json $serializer = null,
        private ?RuleExtensionFactory $extensionFactory = null
    ) {
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->extensionFactory = $extensionFactory ?:
            ObjectManager::getInstance()->get(RuleExtensionFactory::class);
    }

    /**
     * Converts Sale Rule model to Sale Rule DTO
     *
     * @param Rule $ruleModel
     * @return RuleDataModel
     */
    public function toDataModel(Rule $ruleModel)
    {
        $modelData = $ruleModel->getData();
        $modelData = $this->convertExtensionAttributesToObject($modelData);

        /** @var RuleDataModel $dataModel */
        $dataModel = $this->ruleDataFactory->create(['data' => $modelData]);

        $this->mapFields($dataModel, $ruleModel);

        return $dataModel;
    }

    /**
     * @param RuleDataModel $dataModel
     * @param Rule $ruleModel
     * @return $this
     */
    protected function mapConditions(RuleDataModel $dataModel, Rule $ruleModel)
    {
        $conditionSerialized = $ruleModel->getConditionsSerialized();
        if ($conditionSerialized) {
            $conditionArray = $this->serializer->unserialize($conditionSerialized);
            $conditionDataModel = $this->arrayToConditionDataModel($conditionArray);
            $dataModel->setCondition($conditionDataModel);
        } else {
            $dataModel->setCondition(null);
        }
        return $this;
    }

    /**
     * @param RuleDataModel $dataModel
     * @param Rule $ruleModel
     * @return $this
     */
    protected function mapActionConditions(RuleDataModel $dataModel, Rule $ruleModel)
    {
        $actionConditionSerialized = $ruleModel->getActionsSerialized();
        if ($actionConditionSerialized) {
            $actionConditionArray = $this->serializer->unserialize($actionConditionSerialized);
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
                case Rule::COUPON_TYPE_NO_COUPON:
                    $mappedValue = RuleInterface::COUPON_TYPE_NO_COUPON;
                    break;
                case Rule::COUPON_TYPE_SPECIFIC:
                    $mappedValue = RuleInterface::COUPON_TYPE_SPECIFIC_COUPON;
                    break;
                case Rule::COUPON_TYPE_AUTO:
                    $mappedValue = RuleInterface::COUPON_TYPE_AUTO;
                    break;
                default:
            }
            $dataModel->setCouponType($mappedValue);
        }
        return $this;
    }

    /**
     * Convert extension attributes of model to object if it is an array
     *
     * @param array $data
     * @return array
     */
    private function convertExtensionAttributesToObject(array $data)
    {
        if (isset($data['extension_attributes']) && is_array($data['extension_attributes'])) {
            /** @var RuleExtensionInterface $attributes */
            $data['extension_attributes'] = $this->extensionFactory->create(['data' => $data['extension_attributes']]);
        }
        return $data;
    }

    /**
     * @param RuleDataModel $dataModel
     * @param Rule $ruleModel
     * @return $this
     */
    protected function mapFields(RuleDataModel $dataModel, Rule $ruleModel)
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
        /** @var ConditionDataModel $conditionDataModel */
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
