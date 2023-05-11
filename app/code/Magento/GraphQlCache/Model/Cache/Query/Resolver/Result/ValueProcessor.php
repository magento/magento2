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
     * @var DehydratorProviderInterface
     */
    private DehydratorProviderInterface $dehydratorProvider;

    /**
     * @param HydratorProviderInterface $hydratorProvider
     * @param DehydratorProviderInterface $dehydratorProvider
     */
    public function __construct(
        HydratorProviderInterface $hydratorProvider,
        DehydratorProviderInterface $dehydratorProvider
    ) {
        $this->hydratorProvider = $hydratorProvider;
        $this->dehydratorProvider = $dehydratorProvider;
    }

    /**
     * @inheritdoc
     */
    public function processCachedValueAfterLoad(ResolverInterface $resolver, string $cacheKey, &$value): void
    {
        if ($value === null) {
            return;
        }
        $hydrator = $this->hydratorProvider->getHydratorForResolver($resolver);
        if ($hydrator) {
            $this->hydrators[$cacheKey] = $hydrator;
            $value[self::VALUE_HYDRATION_REFERENCE_KEY] = $cacheKey;
        }
    }

    /**
     * @inheritdoc
     */
    public function preProcessParentResolverValue(&$value): void
    {
        $key = $value[self::VALUE_HYDRATION_REFERENCE_KEY] ?? null;
        if ($value && $key) {
            if (isset($this->processedValues[$key])) {
                $value = $this->processedValues[$key];
            } elseif (isset($this->hydrators[$key]) && $this->hydrators[$key] instanceof HydratorInterface) {
                $this->hydrators[$key]->hydrate($value);
                unset($value[self::VALUE_HYDRATION_REFERENCE_KEY]);
                $this->processedValues[$key] = $value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function preProcessValueBeforeCacheSave(ResolverInterface $resolver, ?array &$value): void
    {
        $dehydrator = $this->dehydratorProvider->getDehydratorForResolver($resolver);
        if ($dehydrator) {
            $dehydrator->dehydrate($value);
        }
    }
}
