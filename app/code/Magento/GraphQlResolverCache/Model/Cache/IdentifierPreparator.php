<?php

namespace Magento\GraphQlResolverCache\Model\Cache;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Cache\KeyCalculator\ProviderInterface;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\Type as GraphQlResolverCache;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\ValueProcessorInterface;

class IdentifierPreparator
{
    private \Magento\Framework\Serialize\SerializerInterface $serializer;

    private ProviderInterface $cacheKeyCalculatorProvider;

    private ValueProcessorInterface $valueProcessor;

    private $config;

    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        ProviderInterface $keyCalculatorProvider,
        ValueProcessorInterface $valueProcessor,
        array $config = []
    ) {
        $this->serializer = $serializer;
        $this->cacheKeyCalculatorProvider = $keyCalculatorProvider;
        $this->valueProcessor = $valueProcessor;
        $this->config = $config;
    }

    /**
     * Generate cache key incorporating factors from parameters.
     *
     * @param ResolverInterface $resolver
     * @param array|null $args
     * @param array|null $value
     *
     * @return string
     */
    public function prepareCacheIdentifier(
        ResolverInterface $resolver,
        ?array $args,
        ?array $value
    ): string {
        $queryPayloadHash = sha1(get_class($resolver) . $this->serializer->serialize($args ?? []));

        $preprocessValue = true;
        foreach ($this->getResolverClassChain($resolver) as $class) {
            if (isset($this->config['skipValuePreprocessing'][$class])) {
                $preprocessValue = false;
                break;
            }
        }

        if ($preprocessValue) {
            $this->valueProcessor->preProcessParentResolverValue($value);
        }

        return GraphQlResolverCache::CACHE_TAG
            . '_'
            . $this->cacheKeyCalculatorProvider->getKeyCalculatorForResolver($resolver)->calculateCacheKey($value)
            . '_'
            . $queryPayloadHash;
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
