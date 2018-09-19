<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ResolverInterface;

/**
 * Composite resolver for resolving field type.
 */
class CompositeResolver implements ResolverInterface
{
    /**
     * @var ResolverInterface[]
     */
    private $items;

    /**
     * @param ResolverInterface[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldType(AttributeAdapter $attribute): ?string
    {
        $result = null;
        foreach ($this->items as $item) {
            $result = $item->getFieldType($attribute);
            if (null !== $result) {
                break;
            }
        }

        return $result;
    }
}
