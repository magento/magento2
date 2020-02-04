<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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
     * @param ResolverInterface[] $items
     */
    public function __construct(array $items)
    {
        foreach ($items as $item) {
            if (!$item instanceof ResolverInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Instance of the field name resolver is expected, got %s instead.', get_class($item))
                );
            }
        }
        $this->items = $items;
    }

    /**
     * Get field name.
     *
     * @param AttributeAdapter $attribute
     * @param array $context
     * @return string
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
