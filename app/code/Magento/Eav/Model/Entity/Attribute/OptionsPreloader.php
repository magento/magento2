<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use ReflectionMethod;
use Throwable;

/**
 * Prepare table source options on copies of attributes for Web API output.
 */
class OptionsPreloader implements ResetAfterRequestInterface
{
    /**
     * Source classes that inherit the table implementation.
     *
     * @var array<string, bool>
     */
    private array $tableSources = [];

    /**
     * Initialize option collection and store dependencies.
     *
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly CollectionFactory $collectionFactory,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Return copies of eligible attributes with options grouped by source store.
     *
     * @param AttributeInterface[] $attributes
     * @return AttributeInterface[]
     */
    public function preload(array $attributes): array
    {
        $attributesByStore = [];
        $eligibleAttributes = [];
        foreach ($attributes as $key => $attribute) {
            $candidate = $this->createCandidate($attribute);
            if ($candidate === null) {
                continue;
            }
            $sourceAttribute = $this->getSourceAttribute($candidate);
            if ($sourceAttribute === null) {
                continue;
            }
            $storeId = $sourceAttribute->getStoreId();
            if ($storeId === null) {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $attributesByStore[$storeId][$key] = $sourceAttribute->getId();
            $eligibleAttributes[$key] = $candidate;
        }

        foreach ($attributesByStore as $storeId => $attributeIds) {
            $options = $this->loadOptions($attributeIds, $storeId);
            foreach ($attributeIds as $key => $attributeId) {
                $attributeOptions = $options[$attributeId] ?? [];
                array_unshift($attributeOptions, ['label' => ' ', 'value' => '']);
                $copy = $eligibleAttributes[$key];
                $copy->setData('options', $attributeOptions);
                $attributes[$key] = $copy;
            }
        }
        return $attributes;
    }

    /**
     * Clone only source-bearing models without explicitly prepared options.
     *
     * @param AttributeInterface $attribute
     * @return AbstractAttribute|null
     */
    private function createCandidate(AttributeInterface $attribute): ?AbstractAttribute
    {
        if (!$attribute instanceof AbstractAttribute || !$attribute->usesSource() || $attribute->getData('options')) {
            return null;
        }
        return clone $attribute;
    }

    /**
     * Resolve the source attribute without making a broken source fatal to the service.
     *
     * @param AbstractAttribute $attribute
     * @return AbstractAttribute|null
     */
    private function getSourceAttribute(AbstractAttribute $attribute): ?AbstractAttribute
    {
        try {
            $source = $attribute->getSource();
        } catch (Throwable) {
            return null;
        }
        if (!$source instanceof Table || !$this->inheritsTableOptions($source)) {
            return null;
        }
        return $source->getAttribute();
    }

    /**
     * Check each source class once for an overridden options method.
     *
     * @param Table $source
     * @return bool
     */
    private function inheritsTableOptions(Table $source): bool
    {
        $className = $source::class;
        return $this->tableSources[$className] ??= (new ReflectionMethod($source, 'getAllOptions'))
            ->getDeclaringClass()->getName() === Table::class;
    }

    /**
     * Load options with the table source's ordering and default label fallback.
     *
     * @param array $attributeIds
     * @param int|string $storeId
     * @return array
     */
    private function loadOptions(array $attributeIds, int|string $storeId): array
    {
        $collection = $this->collectionFactory->create()
            ->setPositionOrder('asc')
            ->setAttributeFilter(['in' => array_values(array_unique($attributeIds))])
            ->setStoreFilter($storeId);

        $options = [];
        foreach ($collection as $option) {
            $options[$option->getData('attribute_id')][] = [
                'value' => $option->getData('option_id'),
                'label' => $option->getData('value'),
            ];
        }
        return $options;
    }

    /**
     * Clear source class eligibility between requests.
     *
     * @return void
     */
    public function _resetState(): void
    {
        $this->tableSources = [];
    }
}
