<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;

/**
 * Composite class for resolving field name.
 */
class CompositeResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface[]
     */
    private $items;

    /**
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        $result = null;
        foreach ($this->items as $item) {
            $result = $item->getFieldName($attribute, $context);
            if (null !== $result) {
                break;
            }
        }

        return $result;
    }
}
