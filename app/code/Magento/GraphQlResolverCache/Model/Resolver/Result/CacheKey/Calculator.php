<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey;

use Magento\Framework\ObjectManagerInterface;
use Magento\GraphQl\Model\Query\ContextFactoryInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\ValueProcessorInterface;

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
     * @var GenericFactorProviderInterface[]
     */
    private $factorProviderInstances;

    /**
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * @var ValueProcessorInterface
     */
    private ValueProcessorInterface $valueProcessor;

    /**
     * @param ContextFactoryInterface $contextFactory
     * @param ObjectManagerInterface $objectManager
     * @param ValueProcessorInterface $valueProcessor
     * @param string[] $factorProviders
     */
    public function __construct(
        ContextFactoryInterface $contextFactory,
        ObjectManagerInterface $objectManager,
        ValueProcessorInterface $valueProcessor,
        array $factorProviders = []
    ) {
        $this->contextFactory = $contextFactory;
        $this->factorProviders = $factorProviders;
        $this->objectManager = $objectManager;
        $this->valueProcessor = $valueProcessor;
    }

    /**
     * Calculates the value of resolver cache identifier.
     *
     * @param array|null $parentData
     *
     * @return string|null
     *
     * @throws CalculationException
     */
    public function calculateCacheKey(?array $parentData = null): ?string
    {
        if (!$this->factorProviders) {
            return null;
        }
        try {
            $this->initializeFactorProviderInstances();
            $factors = $this->getFactors($parentData);
            $keysString = strtoupper(implode('|', array_values($factors)));
            return hash('sha256', $keysString);
        } catch (\Throwable $e) {
            throw new CalculationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get key factors from parent data for current context.
     *
     * @param array|null $parentData
     * @return array
     */
    private function getFactors(?array $parentData): array
    {
        $factors = [];
        $context = $this->contextFactory->get();
        foreach ($this->factorProviderInstances as $factorProvider) {
            if ($factorProvider instanceof ParentValueFactorProviderInterface && is_array($parentData)) {
                // preprocess data if the data was fetched from cache and has reference key
                // and the factorProvider expects processed data (original data from resolver)
                if (isset($parentData[ValueProcessorInterface::VALUE_PROCESSING_REFERENCE_KEY])
                   && $factorProvider->isRequiredOrigData()
                ) {
                    $this->valueProcessor->preProcessParentValue($parentData);
                }
                // fetch factor value considering parent data
                $factors[$factorProvider->getFactorName()] = $factorProvider->getFactorValue(
                    $context,
                    $parentData
                );
            } else {
                // get factor value considering only context
                $factors[$factorProvider->getFactorName()] = $factorProvider->getFactorValue(
                    $context
                );
            }
        }
        ksort($factors);
        return $factors;
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
