<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Provides hydrators and dehydrators for the given resolver.
 */
class HydratorDehydratorProvider implements HydratorProviderInterface, DehydratorProviderInterface
{
    /**
     * @var array
     */
    private array $dehydratorConfig = [];

    /**
     * @var DehydratorInterface[]
     */
    private array $dehydratorInstances = [];

    /**
     * @var array
     */
    private array $hydratorConfig = [];

    /**
     * @var HydratorInterface[]
     */
    private array $hydratorInstances = [];

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $hydratorConfig
     * @param array $dehydratorConfig
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $hydratorConfig = [],
        array $dehydratorConfig = []
    ) {
        $this->objectManager = $objectManager;
        $this->dehydratorConfig = $dehydratorConfig;
        $this->hydratorConfig = $hydratorConfig;
    }

    /**
     * @inheritdoc
     */
    public function getDehydratorForResolver(ResolverInterface $resolver): ?DehydratorInterface
    {
        $resolverClass = $this->getResolverClass($resolver);
        if (isset($this->dehydratorInstances[$resolverClass])) {
            return $this->dehydratorInstances[$resolverClass];
        }
        $resolverDehydrators = $this->getInstancesForResolver($resolver, $this->dehydratorConfig);
        if (!empty($resolverDehydrators)) {
            $this->dehydratorInstances[$resolverClass] = $this->objectManager->create(
                DehydratorComposite::class,
                [
                    'dehydrators' => $resolverDehydrators
                ]
            );
        }
        return $this->dehydratorInstances[$resolverClass] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function getHydratorForResolver(ResolverInterface $resolver): ?HydratorInterface
    {
        $resolverClass = $this->getResolverClass($resolver);
        if (isset($this->hydratorInstances[$resolverClass])) {
            return $this->hydratorInstances[$resolverClass];
        }
        $resolverHydrators = $this->getInstancesForResolver($resolver, $this->hydratorConfig);
        if (!empty($resolverHydrators)) {
            $this->hydratorInstances[$resolverClass] = $this->objectManager->create(
                HydratorComposite::class,
                [
                    'hydrators' => $resolverHydrators
                ]
            );
        }
        return $this->hydratorInstances[$resolverClass] ?? null;
    }

    /**
     * Get resolver instance class name.
     *
     * @param ResolverInterface $resolver
     * @return string
     */
    private function getResolverClass(ResolverInterface $resolver): string
    {
        return trim(get_class($resolver), '\\');
    }

    /**
     * Get hydrator or dehydrator instances for the given resolver from given configuration.
     *
     * @param ResolverInterface $resolver
     * @param array $classesConfig
     * @return array
     */
    private function getInstancesForResolver(ResolverInterface $resolver, array $classesConfig): array
    {
        $resolverClassesConfig = [];
        foreach ($this->getResolverClassChain($resolver) as $resolverClass) {
            if (isset($classesConfig[$resolverClass])) {
                $resolverClassesConfig[$resolverClass] = $classesConfig[$resolverClass];
            }
        }
        if (empty($resolverClassesConfig)) {
            $this->dehydratorInstances[$this->getResolverClass($resolver)] = null;
            return [];
        }
        $dataProcessingClassList = [];
        foreach ($resolverClassesConfig as $classChain) {
            foreach ($classChain as $classData) {
                $dataProcessingClassList[] = $classData;
            }
        }
        usort($dataProcessingClassList, function ($data1, $data2) {
            return ((int)$data1['sortOrder'] > (int)$data2['sortOrder']) ? 1 : -1;
        });
        $dataProcessingInstances = [];
        foreach ($dataProcessingClassList as $classData) {
            $dataProcessingInstances[] = $this->objectManager->get($classData['class']);
        }
        return $dataProcessingInstances;
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
