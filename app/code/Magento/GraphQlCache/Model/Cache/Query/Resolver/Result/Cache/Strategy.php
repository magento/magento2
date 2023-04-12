<?php

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculatorFactory;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;
use Magento\GraphQlCache\Model\CacheId\InitializableCacheIdFactorProviderInterface;

class Strategy implements StrategyInterface
{
    private array $customFactorProviders = [];

    private array $factorProviderInstances = [];

    private array $resolverCacheIdCalculatorsInitialized = [];

    private ConfigInterface $objectManagerConfig;

    private CacheIdCalculatorFactory $cacheIdCalculatorFactory;

    /**
     * @param ConfigInterface $objectManagerConfig
     * @param CacheIdCalculatorFactory $cacheIdCalculatorFactory
     * @param array $customFactorProviders
     */
    public function __construct(
        ConfigInterface $objectManagerConfig,
        CacheIdCalculatorFactory $cacheIdCalculatorFactory,
        array $customFactorProviders = []
    ) {
        $this->customFactorProviders = $customFactorProviders;
        $this->objectManagerConfig = $objectManagerConfig;
        $this->cacheIdCalculatorFactory = $cacheIdCalculatorFactory;
    }

    public function initForResolver(ResolverInterface $resolver): void
    {
        $resolverClass = trim(get_class($resolver), '\\');
        $customProviders = $this->getCustomProvidersForResolverObject($resolver);
        $this->factorProviderInstances[$resolverClass] = [];
        $arguments = $this->objectManagerConfig->getArguments(CacheIdCalculator::class);
        if (isset($arguments['idFactorProviders']) && is_array($arguments['idFactorProviders'])) {
            foreach ($arguments['idFactorProviders'] as $key => $idFactorProvider) {
                $instance = $idFactorProvider['instance'];
                if (isset($customProviders['suppress'][$key])
                    || isset($customProviders['suppress'][$instance])
                ) {
                    unset($arguments['idFactorProviders'][$key]);
                } else {
                    $this->factorProviderInstances[$resolverClass][$key] = ObjectManager::getInstance()->get($instance);
                }
            }
        }
        if (isset($customProviders['append']) && is_array($customProviders['append'])) {
            foreach ($customProviders['append'] as $key => $customProviderInstance) {
                if (!isset($customFactorProviders['suppress'][$key])
                    && !isset($customFactorProviders['suppress'][get_class($customProviderInstance)])
                ) {
                    $this->factorProviderInstances[$resolverClass][$key] = $customProviderInstance;
                }
            }
        }

        $this->resolverCacheIdCalculatorsInitialized[$resolverClass] =
            $this->cacheIdCalculatorFactory->create(
                $this->factorProviderInstances[$resolverClass]
            );
    }

    public function getForResolver(ResolverInterface $resolver): CacheIdCalculator
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (!isset($this->resolverCacheIdCalculatorsInitialized[$resolverClass])) {
            $this->initForResolver($resolver);
        }
        return $this->resolverCacheIdCalculatorsInitialized[$resolverClass];
    }

    private function getResolverClassChain(ResolverInterface $resolver): array
    {
        $resolverClasses = [trim(get_class($resolver), '\\')];
        foreach (class_parents($resolver) as $classParent) {
            $resolverClasses[] = trim($classParent, '\\');
        }
        return $resolverClasses;
    }

    private function getCustomProvidersForResolverObject(ResolverInterface $resolver): array
    {
        foreach ($this->getResolverClassChain($resolver) as $resolverClass) {
            if (!empty($this->customFactorProviders[$resolverClass])) {
                return $this->customFactorProviders[$resolverClass];
            }
        }
        return [];
    }

    public function restateFromResolverResult(array $result): void
    {
        foreach ($this->factorProviderInstances as $factorProviderInstance) {
            if ($factorProviderInstance instanceof InitializableCacheIdFactorProviderInterface) {
                $factorProviderInstance->initFromResolvedData($result);
            }
        }
    }

    public function restateFromContext(\Magento\GraphQl\Model\Query\ContextInterface $context): void
    {
        foreach ($this->factorProviderInstances as $factorProviderInstance) {
            if ($factorProviderInstance instanceof InitializableCacheIdFactorProviderInterface) {
                $factorProviderInstance->initFromContext($context);
            }
        }
    }
}
