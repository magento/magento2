<?php

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\InitializableCacheIdFactorProviderInterface;
use Magento\GraphQlCache\Model\Resolver\Cache\CacheIdCalculator;
use Magento\GraphQlCache\Model\Resolver\Cache\CacheIdCalculatorFactory;

class Strategy implements StrategyInterface
{
    /**
     * @var array
     */
    private array $customFactorProviders = [];

    /**
     * @var array
     */
    private array $factorProviderInstances = [];

    /**
     * @var array
     */
    private array $resolverCacheIdCalculatorsInitialized = [];

    /**
     * @var CacheIdCalculator
     */
    private CacheIdCalculator $genericCacheIdCalculator;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $objectManagerConfig;

    /**
     * @var CacheIdCalculatorFactory
     */
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

    /**
     * @param ResolverInterface $resolver
     * @return void
     */
    public function initForResolver(ResolverInterface $resolver): void
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (isset($this->resolverCacheIdCalculatorsInitialized[$resolverClass])) {
            return;
        }
        $customProviders = $this->getCustomProvidersForResolverObject($resolver);
        if (empty($customProviders)) {
            if (empty($this->genericCacheIdCalculator)) {
                $this->genericCacheIdCalculator = $this->cacheIdCalculatorFactory->create();
            }
            $this->resolverCacheIdCalculatorsInitialized[$resolverClass] = $this->genericCacheIdCalculator;
        }
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
                    //todo create workaround for inheritance chain
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

    /**
     * @param ResolverInterface $resolver
     * @return CacheIdCalculator
     */
    public function getCacheIdCalculatorForResolver(ResolverInterface $resolver): CacheIdCalculator
    {
        $resolverClass = trim(get_class($resolver), '\\');
        if (!isset($this->resolverCacheIdCalculatorsInitialized[$resolverClass])) {
            $this->initForResolver($resolver);
        }
        return $this->resolverCacheIdCalculatorsInitialized[$resolverClass];
    }

    /**
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

    /**
     * @param ResolverInterface $resolver
     * @return array
     */
    private function getCustomProvidersForResolverObject(ResolverInterface $resolver): array
    {
        foreach ($this->getResolverClassChain($resolver) as $resolverClass) {
            if (!empty($this->customFactorProviders[$resolverClass])) {
                return $this->customFactorProviders[$resolverClass];
            }
        }
        return [];
    }

    /**
     * @param array $result
     * @return void
     */
    public function restateFromPreviousResolvedValues(ResolverInterface $resolverObject, ?array $result): void
    {
        if (!is_array($result)) {
            return;
        }
        $resolverClass = trim(get_class($resolverObject), '\\');
        if (!isset($this->factorProviderInstances[$resolverClass])) {
            return;
        }
        foreach ($this->factorProviderInstances[$resolverClass] as $factorProviderInstance) {
            if ($factorProviderInstance instanceof InitializableCacheIdFactorProviderInterface) {
                $factorProviderInstance->initFromResolvedData($result);
            }
        }
    }

    /**
     * @param ContextInterface $context
     * @return void
     */
    public function restateFromContext(ContextInterface $context): void
    {
        foreach ($this->factorProviderInstances as $factorProviderInstance) {
            if ($factorProviderInstance instanceof InitializableCacheIdFactorProviderInterface) {
                $factorProviderInstance->initFromContext($context);
            }
        }
    }
}
