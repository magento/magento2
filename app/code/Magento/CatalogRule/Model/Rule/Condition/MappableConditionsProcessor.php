<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Rule\Condition;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor\ConditionProcessor\CustomConditionProviderInterface;
use Magento\CatalogRule\Model\Rule\Condition\Combine as CombinedCondition;
use Magento\CatalogRule\Model\Rule\Condition\Product as SimpleCondition;

/**
 * Rebuilds catalog price rule conditions tree
 * so only those conditions that can be mapped to search criteria are left
 *
 * Those conditions that can't be mapped are deleted from tree
 * If deleted condition is part of combined condition with OR aggregation all this group will be removed
 */
class MappableConditionsProcessor
{
    /**
     * @var CustomConditionProviderInterface
     */
    private $customConditionProvider;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @param CustomConditionProviderInterface $customConditionProvider
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        CustomConditionProviderInterface $customConditionProvider,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->customConditionProvider = $customConditionProvider;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Method to rebuild conditions tree.
     *
     * @param Combine $conditions
     * @return Combine
     */
    public function rebuildConditionsTree(CombinedCondition $conditions): CombinedCondition
    {
        return $this->rebuildCombinedCondition($conditions);
    }

    /**
     * Method to rebuild combined condition.
     *
     * @param Combine $originalConditions
     * @return Combine
     * @throws InputException
     */
    private function rebuildCombinedCondition(CombinedCondition $originalConditions): CombinedCondition
    {
        $validConditions = [];
        $invalidConditions = [];

        foreach ($originalConditions->getConditions() as $condition) {
            if ($condition->getType() === CombinedCondition::class) {
                $rebuildSubCondition = $this->rebuildCombinedCondition($condition);

                if (count($rebuildSubCondition->getConditions()) > 0) {
                    $validConditions[] = $rebuildSubCondition;
                } else {
                    $invalidConditions[] = $rebuildSubCondition;
                }

                continue;
            }

            if ($condition->getType() === SimpleCondition::class) {
                if ($this->validateSimpleCondition($condition)) {
                    $validConditions[] = $condition;
                } else {
                    $invalidConditions[] = $condition;
                }

                continue;
            }

            throw new InputException(
                __('Undefined condition type "%1" passed in.', $condition->getType())
            );
        }
        $aggregator = $originalConditions->getAggregator() ? strtolower($originalConditions->getAggregator()) : '';
        // if resulted condition group has left no mappable conditions - we can remove it at all
        if (count($invalidConditions) > 0 && $aggregator === 'any') {
            $validConditions = [];
        }

        $rebuildCondition = clone $originalConditions;
        $rebuildCondition->setConditions($validConditions);

        return $rebuildCondition;
    }

    /**
     * Method to validate simple condition.
     *
     * @param Product $originalConditions
     * @return bool
     */
    private function validateSimpleCondition(SimpleCondition $originalConditions): bool
    {
        return $this->canUseFieldForMapping($originalConditions->getAttribute());
    }

    /**
     * Checks if condition field is mappable
     *
     * @param string $fieldName
     * @return bool
     */
    private function canUseFieldForMapping(string $fieldName): bool
    {
        // We can map field to search criteria if we have custom processor for it
        if ($this->customConditionProvider->hasProcessorForField($fieldName)) {
            return true;
        }

        // Also we can map field to search criteria if it is an EAV attribute
        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $fieldName);

        // We have this weird check for getBackendType() to verify that attribute really exists
        // because due to eavConfig behavior even if pass non existing attribute code we still receive AbstractAttribute
        // getAttributeId() is not sufficient too because some attributes don't have it - e.g. attribute_set_id
        if ($attribute && $attribute->getBackendType() !== null) {
            return true;
        }

        // In any other cases we can't map field to search criteria
        return false;
    }
}
