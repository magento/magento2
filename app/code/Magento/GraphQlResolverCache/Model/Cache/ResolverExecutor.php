<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlResolverCache\Model\Cache;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\HydrationSkipConfig;
use Magento\GraphQlResolverCache\Model\Cache\Query\Resolver\Result\ValueProcessorInterface;

/**
 * Executes the resolver callable.
 */
class ResolverExecutor
{
    /**
     * @var \Closure
     */
    private \Closure $resolveMethod;

    /**
     * @var ValueProcessorInterface
     */
    private ValueProcessorInterface $valueProcessor;

    /**
     * @var HydrationSkipConfig
     */
    private HydrationSkipConfig $hydrationSkipConfig;

    /**
     * @param \Closure $resolveMethod
     * @param ValueProcessorInterface $valueProcessor
     * @param HydrationSkipConfig $hydrationSkipConfig
     */
    public function __construct(
        \Closure $resolveMethod,
        ValueProcessorInterface $valueProcessor,
        HydrationSkipConfig $hydrationSkipConfig
    ) {
        $this->resolveMethod = $resolveMethod;
        $this->valueProcessor = $valueProcessor;
        $this->hydrationSkipConfig = $hydrationSkipConfig;
    }

    /**
     * Execute the closure for the resolver.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $reflectionClosure = new \ReflectionFunction($this->resolveMethod);
        /** @var ResolverInterface $closureContext */
        $closureContext = $reflectionClosure->getClosureThis();
        if (!$this->hydrationSkipConfig->isSkipForResolvingData($closureContext)) {
            $this->valueProcessor->preProcessParentResolverValue($value);
        }
        return ($this->resolveMethod)($field, $context, $info, $value, $args);
    }
}
