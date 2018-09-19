<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

/**
 * Product fields provider.
 * Provide fields mapping configuration for elasticsearch service of internal product attributes.
 */
interface FieldProviderInterface
{
    /**
     * Get fields.
     *
     * @param array $context
     * @return array
     */
    public function getFields(array $context = []): array;
}
