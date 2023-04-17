<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Cache;

use Exception;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Calculates cache key for the resolver results.
 */
class KeyCalculator
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
     * @param array|null $resolvedData
     *
     * @return string|null
     */
    public function calculateCacheKey(?array $resolvedData = null): ?string
    {
        if (!$this->idFactorProviders) {
            return null;
        }

        try {
            $context = $this->contextFactory->get();
            foreach ($this->idFactorProviders as $idFactorProvider) {
                if ($idFactorProvider instanceof ResolverDependentFactorProviderInterface && $resolvedData !== null) {
                    $keys[$idFactorProvider->getFactorName()] = $idFactorProvider->getFactorValueForResolvedData(
                        $context,
                        $resolvedData
                    );
                } else {
                    $keys[$idFactorProvider->getFactorName()] = $idFactorProvider->getFactorValue($context);
                }
            }
            ksort($keys);
            $keysString = strtoupper(implode('|', array_values($keys)));
            return hash('sha256', $keysString);
        } catch (Exception $e) {
            $this->logger->warning("Unable to obtain cache id for resolver results. " . $e->getMessage());
            return null;
        }
    }
}
