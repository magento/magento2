<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model\Wishlist\BuyRequest;

final class ProductExtensionAttributesStub
{
    /**
     * @param array $options
     */
    public function __construct(private readonly array $options)
    {
    }

    public function getConfigurableProductOptions(): array
    {
        return $this->options;
    }
}
