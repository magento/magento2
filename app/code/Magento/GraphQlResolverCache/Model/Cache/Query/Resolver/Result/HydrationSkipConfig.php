<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result;

use Magento\Framework\GraphQl\Query\ResolverInterface;

class HydrationSkipConfig
{
    /**
     * Hydration skipping configuration.
     *
     * @var array
     */
    private array $config;

    /**
     * @param array $config
     */
    public function __construct(
        array $config = []
    ) {
        $this->config = $config;
    }

    /**
     * Returns true if the given resolver does not need hydrated parent value to resolve data.
     *
     * @param ResolverInterface $resolver
     * @return bool
     */
    public function isSkipForResolvingData(ResolverInterface $resolver): bool
    {
        if (!empty($this->config['skipForResolving'])) {
            return false;
        }
        foreach ($this->getResolverClassChain($resolver) as $class) {
            if (isset($this->config['skipForResolving'][$class])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if the given resolver value cache key can be prepared without the hydrated data.
     *
     * @param ResolverInterface $resolver
     * @return bool
     */
    public function isSkipForKeyCalculation(ResolverInterface $resolver): bool
    {
        if (!empty($this->config['skipForKeyCalculation'])) {
            return false;
        }
        foreach ($this->getResolverClassChain($resolver) as $class) {
            if (isset($this->config['skipForKeyCalculation'][$class])) {
                return true;
            }
        }
        return false;
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
