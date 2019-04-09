<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\GraphQlCache\Model\CacheableQueryHandler;

/**
 * Create @see Value to return data from passed in callback to GraphQL library
 */
class ValueFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CacheableQueryHandler
     */
    private $cacheableQueryHandler;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param CacheableQueryHandler $cacheableQueryHandler
     */
    public function __construct(ObjectManagerInterface $objectManager, CacheableQueryHandler $cacheableQueryHandler)
    {
        $this->objectManager = $objectManager;
        $this->cacheableQueryHandler = $cacheableQueryHandler;
    }

    /**
     * Create value with passed in callback that returns data as parameter
     *
     * @param callable $callback
     * @param ResolveInfo $info
     * @return Value
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function create(callable $callback, Field $field = null, ResolveInfo $info = null) : Value
    {
        /** @var \Magento\Framework\GraphQl\Query\Resolver\Value $value */
        $value = $this->objectManager->create(Value::class, ['callback' => $callback]);
        $value->then(function () use ($value, $field, $info) {
            if (is_array($value->promise->result) && $field) {
                $this->cacheableQueryHandler->handleCacheFromResolverResponse($value->promise->result, $field);
            }
        });
        return $value;
    }
}
