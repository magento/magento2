<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey;

use Exception;
use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;
use Psr\Log\LoggerInterface;

/**
 * Calculates cache key for the resolver results.
 */
class Calculator
{
    /**
     * @var ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @var string[]
     */
    private $factorProviders;

    /**
     * @var FactorProviderInterface[]
     */
    private $factorProviderInstances;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var ValueProcessorInterface
     */
    private ValueProcessorInterface $valueProcessor;

    /**
     * @param LoggerInterface $logger
     * @param ContextFactoryInterface $contextFactory
     * @param ObjectManagerInterface $objectManager
     * @param ValueProcessorInterface $valueProcessor
     * @param string[] $factorProviders
     */
    public function __construct(
        LoggerInterface $logger,
        ContextFactoryInterface $contextFactory,
        ObjectManagerInterface $objectManager,
        ValueProcessorInterface $valueProcessor,
        array $factorProviders = []
    ) {
        $this->logger = $logger;
        $this->contextFactory = $contextFactory;
        $this->factorProviders = $factorProviders;
        $this->objectManager = $objectManager;
        $this->valueProcessor = $valueProcessor;
    }

    /**
     * Calculates the value of resolver cache identifier.
     *
     * @param array|null $parentResolverData
     *
     * @return string|null
     */
    public function calculateCacheKey(?array $parentResolverData = null): ?string
    {
        if (!$this->factorProviders) {
            return null;
        }
        try {
            $context = $this->contextFactory->get();
            $this->initializeFactorProviderInstances();
            $keys = [];
            foreach ($this->factorProviderInstances as $provider) {
                if ($provider instanceof ParentValueFactorProviderInterface) {
                    // trigger data hydration for key calculation
                    // only when the parent-dependent key factor provider is called
                    $this->valueProcessor->preProcessParentValue($parentResolverData);
                    $keys[$provider->getFactorName()] = $provider->getFactorValue(
                        $context,
                        $parentResolverData
                    );
                } else {
                    $keys[$provider->getFactorName()] = $provider->getFactorValue(
                        $context
                    );
                }
            }
            ksort($keys);
            $keysString = strtoupper(implode('|', array_values($keys)));
            return hash('sha256', $keysString);
        } catch (Exception $e) {
            $this->logger->warning("Unable to obtain cache key for resolver results. " . $e->getMessage());
            return null;
        }
    }

    /**
     * Initialize instances of factor providers.
     *
     * @return void
     */
    private function initializeFactorProviderInstances(): void
    {
        if (empty($this->factorProviderInstances) && !empty($this->factorProviders)) {
            foreach ($this->factorProviders as $factorProviderClass) {
                $this->factorProviderInstances[] = $this->objectManager->get($factorProviderClass);
            }
        }
    }
}
