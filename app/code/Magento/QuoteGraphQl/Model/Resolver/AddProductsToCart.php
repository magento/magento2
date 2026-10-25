<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\QuoteGraphQl\Model\AddProductsToCart as AddProductsToCartService;
use Magento\Quote\Model\QuoteMutexInterface;
use Magento\QuoteGraphQl\Model\Cart\ValidateProductCartResolver;

/**
 * Resolver for addProductsToCart mutation
 *
 * @inheritdoc
 */
class AddProductsToCart implements ResolverInterface
{
    /**
     * @param AddProductsToCartService $addProductsToCartService
     * @param QuoteMutexInterface $quoteMutex
     * @param ValidateProductCartResolver $validateCartResolver
     */
    public function __construct(
        private readonly AddProductsToCartService $addProductsToCartService,
        private readonly QuoteMutexInterface $quoteMutex,
        private readonly ValidateProductCartResolver $validateCartResolver
    ) {
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $this->validateCartResolver->execute($args);
        return $this->quoteMutex->execute(
            [$args['cartId']],
            \Closure::fromCallable([$this, 'run']),
            [$context, $args]
        );
    }

    /**
     * Run the resolver.
     *
     * @param ContextInterface $context
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function run($context, ?array $args): array
    {
        return $this->addProductsToCartService->execute($context, $args);
    }
}
