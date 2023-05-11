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
 * Provides dehydrator for the given resolver.
 */
class DehydratorProvider implements DehydratorProviderInterface
{
    /**
     * @var array
     */
    private array $resolverResultDehydrators = [];

    /**
     * @var DehydratorInterface[]
     */
    private array $resolverDehydratorInstances = [];

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param array $resolverResultDehydrators
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        array $resolverResultDehydrators = []
    ) {
        $this->objectManager = $objectManager;
        $this->resolverResultDehydrators = $resolverResultDehydrators;
    }

    /**
     * @inheritdoc
     */
    public function getDehydratorForResolver(ResolverInterface $resolver): ?DehydratorInterface
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (isset($this->resolverDehydratorInstances[$resolverClass])) {
            return $this->resolverDehydratorInstances[$resolverClass];
        }
        $resolverClassDehydrators = $this->getResolverDehydrators($resolver);
        if (empty($resolverClassDehydrators)) {
            $this->resolverDehydratorInstances[$resolverClass] = null;
            return null;
        }
        $dehydratorList = [];
        foreach ($resolverClassDehydrators as $dehydratorChain) {
            foreach ($dehydratorChain as $dehydratorData) {
                $dehydratorList[] = $dehydratorData;
            }
        }
        usort($dehydratorList, function ($data1, $data2) {
            return ((int)$data1['sortOrder'] > (int)$data2['sortOrder']) ? 1 : -1;
        });
        $dehydratorInstances = [];
        foreach ($dehydratorList as $dehydratorData) {
            $dehydratorInstances[] = $this->objectManager->get($dehydratorData['class']);
        }
        $this->resolverDehydratorInstances[$resolverClass] = $this->objectManager->create(
            DehydratorComposite::class,
            [
                'dehydrators' => $dehydratorInstances
            ]
        );
        return $this->resolverDehydratorInstances[$resolverClass];
    }

    /**
     * Get hydrators chain for the given resolver and it's ancestors.
     *
     * @param ResolverInterface $resolver
     * @return array
     */
    private function getResolverDehydrators(ResolverInterface $resolver): array
    {
        $result = [];
        foreach ($this->getResolverClassChain($resolver) as $resolverClass) {
            if (isset($this->resolverResultDehydrators[$resolverClass])) {
                $result[$resolverClass] = $this->resolverResultDehydrators[$resolverClass];
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
