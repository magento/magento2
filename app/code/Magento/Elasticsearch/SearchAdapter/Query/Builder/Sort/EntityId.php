<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Query\Builder\Sort;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\Search\RequestInterface;

class EntityId implements ExpressionBuilderInterface
{
    /**
     * @inheritdoc
     */
    public function build(AttributeAdapter $attribute, string $direction, RequestInterface $request): array
    {
        return [
            '_script' => [
                'type' => 'number',
                'script' => [
                    'lang' => 'painless',
                    'source' => 'Long.parseLong(doc[\'_id\'].value)',
                ],
                'order' => $direction,
            ],
        ];
    }
}
