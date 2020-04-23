<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation;

use Magento\Framework\Api\Search\AggregationInterface;

/**
 * @inheritdoc
 */
class LayerBuilder implements LayerBuilderInterface
{
    /**
     * @var LayerBuilderInterface[]
     */
    private $builders;

    /**
     * @param LayerBuilderInterface[] $builders
     */
    public function __construct(array $builders)
    {
        $this->builders = $builders;
    }

    /**
     * @inheritdoc
     */
    public function build(AggregationInterface $aggregation, ?int $storeId): array
    {
        $layers = [];
        foreach ($this->builders as $builder) {
            $layers[] = $builder->build($aggregation, $storeId);
        }
        $layers = \array_merge(...$layers);

        return \array_filter($layers);
    }
}
