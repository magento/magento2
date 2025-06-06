<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\Price;

/**
 * Pool of price providers for different product types
 */
class ProviderPool
{
    private const DEFAULT = 'default';

    /**
     * @var ProviderInterface[]
     */
    private $providers;

    /**
     * @param ProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * Get price provider by product type
     *
     * @param string $productType
     * @return ProviderInterface
     */
    public function getProviderByProductType(string $productType): ProviderInterface
    {
        if (isset($this->providers[$productType])) {
            return $this->providers[$productType];
        }
        return $this->providers[self::DEFAULT];
    }
}
