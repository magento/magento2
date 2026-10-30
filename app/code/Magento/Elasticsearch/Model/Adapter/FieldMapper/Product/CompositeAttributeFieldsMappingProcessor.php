<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

/**
 * Composite processor for attribute fields mapping
 */
class CompositeAttributeFieldsMappingProcessor implements AttributeFieldsMappingProcessorInterface
{
    /**
     * @param AttributeFieldsMappingProcessorInterface[] $processors
     */
    public function __construct(
        private readonly array $processors = []
    ) {
        foreach ($processors as $processor) {
            if (!$processor instanceof AttributeFieldsMappingProcessorInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Instance of %s is expected, got %s instead.',
                        AttributeFieldsMappingProcessorInterface::class,
                        get_class($processor)
                    )
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function process(string $attributeCode, array $mapping, array $context = []): array
    {
        foreach ($this->processors as $processor) {
            $mapping = $processor->process($attributeCode, $mapping, $context);
        }
        return $mapping;
    }
}
