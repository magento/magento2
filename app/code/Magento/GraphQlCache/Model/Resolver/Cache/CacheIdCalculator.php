<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GraphQlCache\Model\Resolver\Cache;

use Exception;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQlCache\Model\CacheId\CacheIdFactorProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * Generator for the X-Magento-Cache-Id header value used as a cache key
 */
class CacheIdCalculator
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
     * Calculates the value of X-Magento-Cache-Id
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
}
