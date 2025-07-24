<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagSetter\FlagSetterInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessor\FlagGetter\FlagGetterInterface;

/**
 * Value processor for cached resolver value.
 */
class ValueProcessor implements ValueProcessorInterface, ResetAfterRequestInterface
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
    private array $typeConfig;

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
     * @param FlagGetterInterface $defaultFlagGetter
     * @param FlagSetterInterface $defaultFlagSetter
     * @param array $typeConfig
     */
    public function __construct(
        HydratorProviderInterface $hydratorProvider,
        DehydratorProviderInterface $dehydratorProvider,
        ObjectManagerInterface $objectManager,
        FlagGetterInterface $defaultFlagGetter,
        FlagSetterInterface $defaultFlagSetter,
        array $typeConfig = []
    ) {
        $this->hydratorProvider = $hydratorProvider;
        $this->dehydratorProvider = $dehydratorProvider;
        $this->typeConfig = $typeConfig;
        $this->objectManager = $objectManager;
        $this->defaultFlagGetter = $defaultFlagGetter;
        $this->defaultFlagSetter = $defaultFlagSetter;
    }

    /**
     * Get flag setter for the resolver return type.
     *
     * @param ResolveInfo $info
     * @return FlagSetterInterface
     */
    private function getFlagSetterForType(ResolveInfo $info): FlagSetterInterface
    {
        if (isset($this->typeConfig['setters'][get_class($info->returnType)])) {
            return $this->objectManager->get(
                $this->typeConfig['setters'][get_class($info->returnType)]
            );
        }
        return $this->defaultFlagSetter;
    }

    /**
     * @inheritdoc
     */
    public function processCachedValueAfterLoad(
        ResolveInfo $info,
        ResolverInterface $resolver,
        string $cacheKey,
        &$value
    ): void {
        if ($value === null) {
            return;
        }
        $hydrator = $this->hydratorProvider->getHydratorForResolver($resolver);
        if ($hydrator) {
            $this->hydrators[$cacheKey] = $hydrator;
            $hydrator->prehydrate($value);
            $this->getFlagSetterForType($info)->setFlagOnValue($value, $cacheKey);
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
     * @param array $value
     * @return void
     */
    private function hydrateData(array &$value): void
    {
        // the parent value is always a single object that contains currently resolved value
        $reference = $this->defaultFlagGetter->getFlagFromValue($value) ?? null;
        if (isset($reference['cacheKey']) && isset($reference['index'])) {
            $cacheKey = $reference['cacheKey'];
            $index = $reference['index'];
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
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->hydrators = [];
        $this->processedValues = [];
    }
}
