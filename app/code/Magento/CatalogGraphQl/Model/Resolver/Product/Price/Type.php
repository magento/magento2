<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\Price;

use Magento\Framework\ObjectManagerInterface;

/**
 * Use to the retrieve correct price provider
 */
class Type
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Store created provider objects
     *
     * @var array
     */
    private $providers = [];

    /**
     * @var array
     */
    private $types = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $types
     */
    public function __construct(ObjectManagerInterface $objectManager, array $types)
    {
        $this->objectManager = $objectManager;
        $this->types = array_merge($this->types, $types);
    }

    /**
     * Get appropriate price provider based on product type
     *
     * @param string $productType
     * @return ProviderInterface
     */
    public function getProviderByProductType(string $productType): ProviderInterface
    {
        $providerType = $this->types['default'];

        if (isset($this->types[$productType])) {
            $providerType = $this->types[$productType];
        }

        if (!isset($this->providers[$providerType])) {
            $this->providers[$providerType] = $this->objectManager->get($providerType);
        }

        return $this->providers[$providerType];
    }
}
