<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagSetter\FlagSetterInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagGetter\FlagGetterInterface;

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
     * @var array
     */
    private array $resolverProcessingFlagConfig;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var FlagGetterInterface
     */
    private FlagGetterInterface $defaultFlagGetter;

    /**
     * @var FlagSetterInterface
     */
    private FlagSetterInterface $defaultFlagSetter;

    /**
     * @param HydratorProviderInterface $hydratorProvider
     * @param DehydratorProviderInterface $dehydratorProvider
     * @param ObjectManagerInterface $objectManager
     * @param array $resolverProcessingFlagConfig
     */
    public function __construct(
        HydratorProviderInterface $hydratorProvider,
        DehydratorProviderInterface $dehydratorProvider,
        ObjectManagerInterface $objectManager,
        array $resolverProcessingFlagConfig = []
    ) {
        $this->hydratorProvider = $hydratorProvider;
        $this->dehydratorProvider = $dehydratorProvider;
        $this->resolverProcessingFlagConfig = $resolverProcessingFlagConfig;
        $this->objectManager = $objectManager;
        $this->defaultFlagGetter = $this->objectManager->get(FlagGetterInterface::class);
        $this->defaultFlagSetter = $this->objectManager->get(FlagSetterInterface::class);
    }

    /**
     * Get flag setter fr the given resolver.
     *
     * @param ResolverInterface $resolver
     *
     * @return FlagSetterInterface
     */
    private function getFlagSetterForResolver(ResolverInterface $resolver): FlagSetterInterface
    {
        foreach ($this->getResolverClassChain($resolver) as $className) {
            if (isset($this->resolverProcessingFlagConfig['setters'][$className])) {
                return $this->objectManager->get(
                    $this->resolverProcessingFlagConfig['setters'][$className]
                );
            }
        }
        return $this->objectManager->get(FlagSetterInterface::class);
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
            $this->getFlagSetterForResolver($resolver)->setFlagOnValue($value, $cacheKey);
        }
    }

    /**
     * @inheritdoc
     */
    public function preProcessParentValue(array &$value): void
    {
        $this->hydrateData($value);
    }

    /**
     * Perform data hydration.
     *
     * @param array|null $value
     * @return void
     */
    private function hydrateData(&$value)
    {
        $reference = $this->defaultFlagGetter->getFlagFromValue($value) ?? null;
        if (isset($reference['cacheKey']) && isset($reference['index'])) {
            $cacheKey = $reference['cacheKey'];
            $index = $reference['index'];
            if ($value && $cacheKey) {
                if (isset($this->processedValues[$cacheKey][$index])) {
                    $value = $this->processedValues[$cacheKey][$index];
                } elseif (isset($this->hydrators[$cacheKey])
                    && $this->hydrators[$cacheKey] instanceof HydratorInterface
                ) {
                    $this->hydrators[$cacheKey]->hydrate($value);
                    $this->defaultFlagSetter->unsetFlagFromValue($value);
                    $this->processedValues[$cacheKey][$index] = $value;
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function preProcessValueBeforeCacheSave(ResolverInterface $resolver, &$value): void
    {
        $dehydrator = $this->dehydratorProvider->getDehydratorForResolver($resolver);
        if ($dehydrator) {
            $dehydrator->dehydrate($value);
        }
    }

    /**
     * Get class inheritance chain for the given resolver object.
     *
     * @param ResolverInterface $resolver
     * @return array
     */
    private function getResolverClassChain(ResolverInterface $resolver): array
    {
        $resolverClasses = [trim(get_class($resolver), '\\')];
        foreach (class_parents($resolver) as $classParent) {
            $resolverClasses[] = trim($classParent, '\\');
        }
        return $resolverClasses;
    }
}
