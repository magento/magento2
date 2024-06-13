<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\ResourceModel;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Framework\Exception\LocalizedException;

/**
 * Service class to clean orphaned multiselect attribute values
 */
class OrphanedMultiselectCleaner
{
    /**
     * Clean orphaned multiselect values from entity
     *
     * @param AbstractEntity $resource
     * @param DataObject $entity
     * @return void
     */
    public function cleanEntity(AbstractEntity $resource, DataObject $entity): void
    {
        $resource->loadAllAttributes($entity);
        $entityData = $entity->getData();

        foreach ($entityData as $attributeCode => $value) {
            $this->cleanAttributeValue($resource, $entity, $attributeCode, $value);
        }
    }

    /**
     * Clean a single multiselect attribute value
     *
     * @param AbstractEntity $resource
     * @param DataObject $entity
     * @param string $attributeCode
     * @param mixed $value
     * @return void
     */
    private function cleanAttributeValue(
        AbstractEntity $resource,
        DataObject $entity,
        string $attributeCode,
        mixed $value
    ): void {
        if (empty($value) && $value !== '0') {
            return;
        }

        if ($entity->dataHasChangedFor($attributeCode)) {
            return;
        }

        try {
            $attribute = $resource->getAttribute($attributeCode);
            if (!$this->isMultiselectAttribute($attribute)) {
                return;
            }

            $values = is_array($value) ? $value : explode(',', (string) $value);
            $validValues = $this->filterValidOptionValues($attribute, $values);

            if (count($validValues) !== count($values)) {
                $entity->unsetData($attributeCode);
                $entity->setData($attributeCode, implode(',', $validValues) ?: null);
            }
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Check if attribute is a multiselect with source model
     *
     * @param mixed $attribute
     * @return bool
     */
    private function isMultiselectAttribute($attribute): bool
    {
        return $attribute
            && $attribute->getFrontendInput() === 'multiselect'
            && $attribute->usesSource();
    }

    /**
     * Filter valid option values from array
     *
     * @param AbstractAttribute $attribute
     * @param array $values
     * @return array
     * @throws LocalizedException
     */
    private function filterValidOptionValues($attribute, array $values): array
    {
        $validValues = [];
        foreach ($values as $optionId) {
            $optionId = trim((string) $optionId);
            if ($optionId !== '' && $attribute->getSource()->getOptionText($optionId) !== false) {
                $validValues[] = $optionId;
            }
        }
        return $validValues;
    }
}
