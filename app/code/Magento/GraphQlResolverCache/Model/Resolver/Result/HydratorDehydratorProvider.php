<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result;

use Magento\Framework\Exception\ConfigurationMismatchException;
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
        if (array_key_exists($resolverClass, $this->dehydratorInstances)) {
            return $this->dehydratorInstances[$resolverClass];
        }
        $resolverDehydrators = $this->getInstancesForResolver(
            $resolver,
            $this->dehydratorConfig,
            DehydratorInterface::class
        );
        if (empty($resolverDehydrators)) {
            $this->dehydratorInstances[$resolverClass] = null;
        } else {
            $this->dehydratorInstances[$resolverClass] = $this->objectManager->create(
                DehydratorComposite::class,
                [
                    'dehydrators' => $resolverDehydrators
                ]
            );
        }
        return $this->dehydratorInstances[$resolverClass];
    }

    /**
     * @inheritDoc
     */
    public function getHydratorForResolver(ResolverInterface $resolver): ?HydratorInterface
    {
        $resolverClass = $this->getResolverClass($resolver);
        if (array_key_exists($resolverClass, $this->hydratorInstances)) {
            return $this->hydratorInstances[$resolverClass];
        }
        $resolverHydrators = $this->getInstancesForResolver(
            $resolver,
            $this->hydratorConfig,
            HydratorInterface::class
        );
        if (empty($resolverHydrators)) {
            $this->hydratorInstances[$resolverClass] = null;
        } else {
            $this->hydratorInstances[$resolverClass] = $this->objectManager->create(
                HydratorComposite::class,
                [
                    'hydrators' => $resolverHydrators
                ]
            );
        }
        return $this->hydratorInstances[$resolverClass];
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
     * @param string $interfaceName
     * @return array
     * @throws ConfigurationMismatchException
     */
    private function getInstancesForResolver(
        ResolverInterface $resolver,
        array $classesConfig,
        string $interfaceName
    ): array {
        $resolverClassesConfig = [];
        foreach ($this->getResolverClassChain($resolver) as $resolverClass) {
            if (isset($classesConfig[$resolverClass])) {
                $resolverClassesConfig[$resolverClass] = $classesConfig[$resolverClass];
            }
        }
        if (empty($resolverClassesConfig)) {
            return [];
        }
        $dataProcessingClassList = [];
        foreach ($resolverClassesConfig as $resolverClass => $classChain) {
            $this->validateClassChain($classChain, $interfaceName, $resolverClass);
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
     * Validate hydrator or dehydrator classes and throw exception if class does not implement relevant interface.
     *
     * @param array $classChain
     * @param string $interfaceName
     * @param string $resolverClass
     * @return void
     * @throws ConfigurationMismatchException
     */
    private function validateClassChain(array $classChain, string $interfaceName, string $resolverClass)
    {
        foreach ($classChain as $classData) {
            if (!is_a($classData['class'], $interfaceName, true)) {
                if ($interfaceName == HydratorInterface::class) {
                    throw new ConfigurationMismatchException(
                        __(
                            'Hydrator %1 configured for resolver %2 must implement %3.',
                            $classData['class'],
                            $resolverClass,
                            $interfaceName
                        )
                    );
                } else {
                    throw new ConfigurationMismatchException(
                        __(
                            'Dehydrator %1 configured for resolver %2 must implement %3.',
                            $classData['class'],
                            $resolverClass,
                            $interfaceName
                        )
                    );
                }

            }
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
