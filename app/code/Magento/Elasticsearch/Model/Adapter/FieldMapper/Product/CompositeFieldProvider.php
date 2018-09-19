<?php

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

/**
 * Provide fields for product.
 */
class CompositeFieldProvider implements FieldProviderInterface
{
    /**
     * @var FieldProviderInterface[]
     */
    private $providers;

    /**
     * @param array $providers
     */
    public function __construct(array $providers)
    {
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
            $allAttributes = array_merge($allAttributes, $provider->getFields($context));
        }

        return $allAttributes;
    }
}
