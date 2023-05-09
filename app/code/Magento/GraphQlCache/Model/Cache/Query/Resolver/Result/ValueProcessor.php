<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Value processor for cached resolver value.
 */
class ValueProcessor implements ValueProcessorInterface
{
    /**
     * @var HydratorProviderInterface
     */
    private HydratorProviderInterface $hydratorProvider;

    /**
     * @var HydratorInterface[]
     */
    private array $hydrators = [];

    /**
     * @var array
     */
    private array $processedValues = [];

    /**
     * @param HydratorProviderInterface $hydratorProvider
     */
    public function __construct(
        HydratorProviderInterface $hydratorProvider
    ) {
        $this->hydratorProvider = $hydratorProvider;
    }

    /**
     * @inheritdoc
     */
    public function postProcessCachedValue(ResolverInterface $resolver, ?string $cacheKey, &$value): void
    {
        $hydrator = $this->hydratorProvider->getHydratorForResolver($resolver);
        if ($hydrator) {
            $this->hydrators[$cacheKey] = $hydrator;
            $value[self::VALUE_PROCESSOR_REFERENCE_KEY] = $cacheKey;
        }
    }

    /**
     * @inheritdoc
     */
    public function preProcessParentResolverValue(&$value): void
    {
        $key = $value[self::VALUE_PROCESSOR_REFERENCE_KEY] ?? null;
        if ($value && $key) {
            if (isset($this->processedValues[$key])) {
                $value = $this->processedValues[$key];
            } else if (isset($this->hydrators[$key]) && $this->hydrators[$key] instanceof HydratorInterface) {
                $this->hydrators[$key]->hydrate($value);
                unset($value[self::VALUE_PROCESSOR_REFERENCE_KEY]);
                $this->processedValues[$key] = $value;
            }
        }
    }
}
