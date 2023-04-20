<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Provides hydrator for the given resolver.
 */
class HydratorProvider implements HydratorProviderInterface
{
    /**
     * @var array
     */
    private array $resolverResultHydrators = [];

    /**
     * @var HydratorInterface[]
     */
    private array $resolverHydratorInstances = [];

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $resolverResultHydrators
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $resolverResultHydrators = []
    ) {
        $this->objectManager = $objectManager;
        $this->resolverResultHydrators = $resolverResultHydrators;
    }

    /**
     * @inheritdoc
     */
    public function getHydratorForResolver(ResolverInterface $resolver): ?HydratorInterface
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (isset($this->resolverHydratorInstances[$resolverClass])) {
            return $this->resolverHydratorInstances[$resolverClass];
        }
        $resolverClassChainHydrators = $this->getResolverHydrators($resolver);
        if (empty($resolverClassChainHydrators)) {
            $this->resolverHydratorInstances[$resolverClass] = null;
            return null;
        }
        $hydratorsList = [];
        foreach ($resolverClassChainHydrators as $hydratorChain) {
            foreach ($hydratorChain as $hydratorData) {
                $hydratorsList[] = $hydratorData;
            }
        }
        usort($hydratorsList, function ($data1, $data2) {
            return ((int)$data1['sortOrder'] > (int)$data2['sortOrder']) ? 1 : -1;
        });
        $hydratorInstances = [];
        foreach ($hydratorsList as $hydratorData) {
            $hydratorInstances[] = $this->objectManager->get($hydratorData['class']);
        }
        $this->resolverHydratorInstances[$resolverClass] = $this->objectManager->create(
            HydratorComposite::class,
            [
                'hydrators' => $hydratorInstances
            ]
        );
        return $this->resolverHydratorInstances[$resolverClass];
    }

    /**
     * Get hydrators chain for the given resolver and it's ancestors.
     *
     * @param ResolverInterface $resolver
     * @return array
     */
    private function getResolverHydrators(ResolverInterface $resolver): array
    {
        $result = [];
        foreach ($this->getResolverClassChain($resolver) as $resolverClass) {
            if (isset($this->resolverResultHydrators[$resolverClass])) {
                $result[$resolverClass] = $this->resolverResultHydrators[$resolverClass];
            }
        }
        return $result;
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
