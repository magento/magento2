<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Model\Plugin\Query;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlCache;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQlCache\Model\CacheableQueryHandler;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;

/**
 * Plugin to cache resolver result where applicable, and handle cache validation that can be done after each resolver
 */
class Resolver
{
    /**
     * @var CacheableQueryHandler
     */
    private $cacheableQueryHandler;

    /**
     * @var GraphQlCache
     */
    private $graphqlCache;

    /**
     * @var CacheIdCalculator
     */
    private $cacheIdCalculator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string[]
     */
    private array $cacheableResolverClassNames;

    /**
     * @param CacheableQueryHandler $cacheableQueryHandler
     * @param GraphQlCache $graphqlCache
     * @param CacheIdCalculator $cacheIdCalculator
     * @param SerializerInterface $serializer
     * @param string[] $cacheableResolverClassNames
     */
    public function __construct(
        CacheableQueryHandler $cacheableQueryHandler,
        GraphQlCache $graphqlCache,
        CacheIdCalculator $cacheIdCalculator,
        SerializerInterface $serializer,
        array $cacheableResolverClassNames = []
    ) {
        $this->cacheableQueryHandler = $cacheableQueryHandler;
        $this->graphqlCache = $graphqlCache;
        $this->cacheIdCalculator = $cacheIdCalculator;
        $this->serializer = $serializer;
        $this->cacheableResolverClassNames = $cacheableResolverClassNames;
    }

    /**
     * TODO - doc
     *
     * @param ResolverInterface $subject
     * @param \Closure $proceed
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return mixed|Value
     */
    public function aroundResolve(
        ResolverInterface $subject,
        \Closure $proceed,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $cacheTagSchema = $field->getCache();
        $hasCacheIdentity = isset($cacheTagSchema['cacheIdentity']);
        $isQuery = $info->operation->operation === 'query';

        $isResolverCacheable = false;

        foreach ($this->cacheableResolverClassNames as $cacheableResolverClassName) {
            $isResolverCacheable = $subject instanceof $cacheableResolverClassName;

            if ($isResolverCacheable) {
                break;
            }
        }

        $isCacheable = $isResolverCacheable && $hasCacheIdentity && $isQuery;

        if (!$isCacheable) {
            return $proceed($field, $context, $info, $value, $args);
        }

        $cacheIdentityFullPageContextString = $this->cacheIdCalculator->getCacheId();
        $cacheIdentityQueryPayloadString = $info->returnType->name . $this->serializer->serialize($args ?? []);

        $cacheIdentityString = $cacheIdentityFullPageContextString . '-' . sha1($cacheIdentityQueryPayloadString);

        $cachedResult = $this->graphqlCache->load($cacheIdentityString);

        if ($cachedResult !== false) {
            return $this->serializer->unserialize($cachedResult);
        }

        $resolvedValue = $proceed($field, $context, $info, $value, $args);

        $cacheIdentityClassName = $cacheTagSchema['cacheIdentity'];

        $tags = $this->cacheableQueryHandler->getTagsByIdentityClassNameAndResolvedValue(
            $cacheIdentityClassName,
            $resolvedValue
        );

        $this->graphqlCache->save(
            $this->serializer->serialize($resolvedValue),
            $cacheIdentityString,
            $tags
        );

        return $resolvedValue;
    }

    /**
     * Set cache validity to the cacheableQuery after resolving any resolver in a query
     *
     * @param ResolverInterface $subject
     * @param mixed|Value $resolvedValue
     * @param Field $field
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterResolve(
        ResolverInterface $subject,
        $resolvedValue,
        Field $field
    ) {
        $cacheAnnotation = $field->getCache();
        if (!empty($cacheAnnotation)) {
            if (is_array($resolvedValue)) {
                $this->cacheableQueryHandler->handleCacheFromResolverResponse(
                    $resolvedValue,
                    $cacheAnnotation
                );
            } elseif ($resolvedValue instanceof Value) {
                $resolvedValue->then(
                    function () use ($resolvedValue, $field, $cacheAnnotation) {
                        if (is_array($resolvedValue->result)) {
                            $this->cacheableQueryHandler->handleCacheFromResolverResponse(
                                $resolvedValue->result,
                                $cacheAnnotation
                            );
                        } else {
                            // case if string or integer we pass in a single array element
                            $this->cacheableQueryHandler->handleCacheFromResolverResponse(
                                $resolvedValue->result === null ?
                                    [] : [$field->getName() => $resolvedValue->result],
                                $cacheAnnotation
                            );
                        }
                    }
                );
            } else {
                // case if string or integer we pass in a single array element
                $this->cacheableQueryHandler->handleCacheFromResolverResponse(
                    $resolvedValue === null ? [] : [$field->getName() => $resolvedValue],
                    $cacheAnnotation
                );
            }
        }
        return $resolvedValue;
    }
}
