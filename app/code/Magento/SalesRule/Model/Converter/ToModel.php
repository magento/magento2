<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Converter;

use DateTime;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\Data\RuleLabelInterface;
use Magento\SalesRule\Model\Data\Condition;
use Magento\SalesRule\Model\Data\Rule as RuleDataModel;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule as ModelRule;
use Magento\SalesRule\Model\RuleFactory;

class ToModel
{
    public const DATE_TIME_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * @param RuleFactory $ruleFactory
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        protected readonly RuleFactory $ruleFactory,
        protected readonly DataObjectProcessor $dataObjectProcessor
    ) {
    }

    /**
     * Map conditions
     *
     * @param ModelRule $ruleModel
     * @param RuleDataModel $dataModel
     * @return $this
     */
    protected function mapConditions(ModelRule $ruleModel, RuleDataModel $dataModel)
    {
        $condition = $dataModel->getCondition();
        if ($condition) {
            $array = $this->dataModelToArray($condition);
            $ruleModel->getConditions()->setConditions([])->loadArray($array);
        }

        return $this;
    }

    /**
     * Map action conditions
     *
     * @param ModelRule $ruleModel
     * @param RuleDataModel $dataModel
     * @return $this
     */
    protected function mapActionConditions(ModelRule $ruleModel, RuleDataModel $dataModel)
    {
        $condition = $dataModel->getActionCondition();
        if ($condition) {
            $array = $this->dataModelToArray($condition, 'actions');
            $ruleModel->getActions()->setActions([])->loadArray($array, 'actions');
        }

        return $this;
    }

    /**
     * Map store labels
     *
     * @param Rule $ruleModel
     * @param RuleDataModel $dataModel
     * @return $this
     */
    protected function mapStoreLabels(Rule $ruleModel, RuleDataModel $dataModel)
    {
        //translate store labels object into array
        if ($dataModel->getStoreLabels() !== null) {
            $storeLabels = [];
            /** @var RuleLabelInterface $ruleLabel */
            foreach ($dataModel->getStoreLabels() as $ruleLabel) {
                $storeLabels[$ruleLabel->getStoreId()] = $ruleLabel->getStoreLabel();
            }
            $ruleModel->setStoreLabels($storeLabels);
        }
        return $this;
    }

    /**
     * Map coupon type
     *
     * @param Rule $ruleModel
     * @return $this
     */
    protected function mapCouponType(Rule $ruleModel)
    {
        if ($ruleModel->getCouponType() && !is_numeric($ruleModel->getCouponType())) {
            $mappedValue = '';
            switch ($ruleModel->getCouponType()) {
                case RuleInterface::COUPON_TYPE_NO_COUPON:
                    $mappedValue = ModelRule::COUPON_TYPE_NO_COUPON;
                    break;
                case RuleInterface::COUPON_TYPE_SPECIFIC_COUPON:
                    $mappedValue = ModelRule::COUPON_TYPE_SPECIFIC;
                    break;
                case RuleInterface::COUPON_TYPE_AUTO:
                    $mappedValue = ModelRule::COUPON_TYPE_AUTO;
                    break;
                default:
            }
            $ruleModel->setCouponType($mappedValue);
        }
        return $this;
    }

    /**
     * Map fields
     *
     * @param ModelRule $ruleModel
     * @param RuleDataModel $dataModel
     * @return $this
     */
    protected function mapFields(ModelRule $ruleModel, RuleDataModel $dataModel)
    {
        $this->mapConditions($ruleModel, $dataModel);
        $this->mapActionConditions($ruleModel, $dataModel);
        $this->mapStoreLabels($ruleModel, $dataModel);
        $this->mapCouponType($ruleModel);
        return $this;
    }

    /**
     * Convert recursive array into condition data model
     *
     * @param Condition $condition
     * @param string $key
     * @return array
     */
    public function dataModelToArray(Condition $condition, $key = 'conditions')
    {
        $output = [];
        $output['type'] = $condition->getConditionType();
        $output['value'] = $condition->getValue();
        $output['attribute'] = $condition->getAttributeName();
        $output['operator'] = $condition->getOperator();

        if ($condition->getAggregatorType()) {
            $output['aggregator'] = $condition->getAggregatorType();
        }
        if ($condition->getConditions()) {
            $conditions = [];
            foreach ($condition->getConditions() as $subCondition) {
                $conditions[] = $this->dataModelToArray($subCondition, $key);
            }
            $output[$key] = $conditions;
        }
        return $output;
    }

    /**
     * To Model
     *
     * @param RuleDataModel $dataModel
     * @return $this|Rule
     * @throws NoSuchEntityException
     * @throws InputException
     */
    public function toModel(RuleDataModel $dataModel)
    {
        $ruleId = $dataModel->getRuleId();

        if ($ruleId) {
            $ruleModel = $this->ruleFactory->create()->load($ruleId);
            if (!$ruleModel->getId()) {
                throw new NoSuchEntityException();
            }
        } else {
            $ruleModel = $this->ruleFactory->create();
            $dataModel->setFromDate(
                $this->formattingDate($dataModel->getFromDate())
            );
            $dataModel->setToDate(
                $this->formattingDate($dataModel->getToDate())
            );
        }

        $modelData = $ruleModel->getData();

        $data = $this->dataObjectProcessor->buildOutputDataArray(
            $dataModel,
            RuleInterface::class
        );

        $data = array_filter($data, function ($value) {
            return $value !== null;
        });
        $mergedData = array_merge($modelData, $data);

        $validateResult = $ruleModel->validateData(new DataObject($mergedData));
        if ($validateResult !== true) {
            $text = '';
            /** @var Phrase $errorMessage */
            foreach ($validateResult as $errorMessage) {
                $text .= $errorMessage->getText();
                $text .= '; ';
            }
            throw new InputException(new Phrase($text));
        }

        $ruleModel->setData($mergedData);

        $this->mapFields($ruleModel, $dataModel);

        return $ruleModel;
    }

    /**
     * Convert date to ISO8601
     *
     * @param string|null $date
     * @return string|null
     */
    private function formattingDate($date)
    {
        if ($date) {
            $fromDate = new DateTime($date);
            $date = $fromDate->format(self::DATE_TIME_FORMAT);
        }

        return $date;
    }
}
