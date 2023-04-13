<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQlCache\Model\Resolver\Cache;

use Exception;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;
use Magento\GraphQlCache\Model\CacheId\InitializableCacheIdFactorProviderInterface;
use Magento\GraphQlCache\Model\CacheId\InitializableInterface;
use Psr\Log\LoggerInterface;

/**
 * Generator for the resolver cache identifier used as a cache key for resolver results
 */
class ResolverCacheIdCalculator implements InitializableInterface
{
    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @var CacheIdFactorProviderInterface[]
     */
    private $idFactorProviders;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param ContextFactoryInterface $contextFactory
     * @param CacheIdFactorProviderInterface[] $idFactorProviders
     */
    public function __construct(
        LoggerInterface $logger,
        ContextFactoryInterface $contextFactory,
        array $idFactorProviders = []
    ) {
        $this->logger = $logger;
        $this->contextFactory = $contextFactory;
        $this->idFactorProviders = $idFactorProviders;
    }

    /**
     * Calculates the value of resolver cache identifier.
     *
     * @return string|null
     */
    public function getCacheId(): ?string
    {
        if (!$this->idFactorProviders) {
            return null;
        }

        try {
            $context = $this->contextFactory->get();
            foreach ($this->idFactorProviders as $idFactorProvider) {
                $keys[$idFactorProvider->getFactorName()] = $idFactorProvider->getFactorValue($context);
            }
            ksort($keys);
            $keysString = strtoupper(implode('|', array_values($keys)));
            return hash('sha256', $keysString);
        } catch (Exception $e) {
            $this->logger->warning("Unable to obtain cache id for resolver results. " . $e->getMessage());
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function initialize(array $resolvedData, ContextInterface $context): void
    {
        foreach ($this->idFactorProviders as $factorProviderInstance) {
            if ($factorProviderInstance instanceof InitializableCacheIdFactorProviderInterface) {
                $factorProviderInstance->initialize($resolvedData, $context);
            }
        }
    }
}
