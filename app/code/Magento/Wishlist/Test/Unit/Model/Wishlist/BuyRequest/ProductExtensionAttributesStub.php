<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model\Wishlist\BuyRequest;

/**
 * Deterministic stand-in for the generated ProductExtensionInterface used by ChildSkuDataProviderTest.
 */
class ProductExtensionAttributesStub
{
    /**
     * @param array $options
     */
    public function __construct(private readonly array $options)
    {
    }

    /**
     * Return the configured configurable product options.
     *
     * @return array
     */
    public function getConfigurableProductOptions(): array
    {
        return $this->options;
    }
}
