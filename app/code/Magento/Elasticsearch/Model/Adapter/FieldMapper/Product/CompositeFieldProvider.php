<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

/**
 * Provide fields for product.
 * @deprecated Elasticsearch is no longer supported by Adobe
 * @see this class will be responsible for ES only
 */
class CompositeFieldProvider implements FieldProviderInterface
{
    /**
     * @var FieldProviderInterface[]
     */
    private $providers;

    /**
     * @param FieldProviderInterface[] $providers
     */
    public function __construct(array $providers)
    {
        foreach ($providers as $provider) {
            if (!$provider instanceof FieldProviderInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Instance of the field provider is expected, got %s instead.', get_class($provider))
                );
            }
        }
        $this->providers = $providers;
    }

    /**
     * Get fields.
     *
     * @param array $context
     * @return array
     */
    public function getFields(array $context = []): array
    {
        $allAttributes = [];

        foreach ($this->providers as $provider) {
            $allAttributes[] = $provider->getFields($context);
        }

        return array_merge([], ...$allAttributes);
    }
}
