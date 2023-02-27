<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\InvalidArgumentException;

class ConfigurableOptionsCompositeFilter implements ConfigurableOptionsFilterInterface
{
    /**
     * @var ConfigurableOptionsFilterInterface[]
     */
    private $configurableOptionsFilters;

    /**
     * @param ConfigurableOptionsFilterInterface[] $configurableOptionsFilters
     * @throws InvalidArgumentException
     */
    public function __construct(
        array $configurableOptionsFilters = []
    ) {
        foreach ($configurableOptionsFilters as $configurableOptionsFilter) {
            if (!$configurableOptionsFilter instanceof ConfigurableOptionsFilterInterface) {
                throw new InvalidArgumentException(
                    __(
                        'Filter %1 doesn\'t implement %2',
                        get_class($configurableOptionsFilter),
                        ConfigurableOptionsFilterInterface::class
                    )
                );
            }
        }
        $this->configurableOptionsFilters = $configurableOptionsFilters;
    }

    /**
     * @inheritdoc
     */
    public function filter(ProductInterface $parentProduct, array $childProducts): array
    {
        foreach ($this->configurableOptionsFilters as $configurableOptionsFilter) {
            $childProducts = $configurableOptionsFilter->filter($parentProduct, $childProducts);
        }
        return $childProducts;
    }
}
