<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

interface AttributeFieldsMappingProcessorInterface
{
    /**
     * Process attribute fields mapping
     *
     * @param string $attributeCode
     * @param array $mapping
     * @param array $context
     * @return array
     */
    public function process(string $attributeCode, array $mapping, array $context = []): array;
}
