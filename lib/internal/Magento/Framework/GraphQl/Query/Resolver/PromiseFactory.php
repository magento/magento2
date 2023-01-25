<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Argument\ValidatorInterface;
use Magento\Framework\GraphQl\Query\Resolver\Factory as ResolverFactory;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfoFactory;

/**
 * Create promises that return resolver results
 */
class PromiseFactory
{
    /**
     * @var Factory
     */
    private $resolverFactory;

    /**
     * @var ResolveInfoFactory
     */
    private $resolveInfoFactory;

    /**
     * @var ValidatorInterface
     */
    private $argumentValidator;

    /**
     * @param Factory $resolverFactory
     * @param ResolveInfoFactory $resolveInfoFactory
     * @param ValidatorInterface $argumentValidator
     */
    public function __construct(
        ResolverFactory $resolverFactory,
        ResolveInfoFactory $resolveInfoFactory,
        ValidatorInterface $argumentValidator
    ) {
        $this->resolverFactory = $resolverFactory;
        $this->resolveInfoFactory = $resolveInfoFactory;
        $this->argumentValidator = $argumentValidator;
    }

    /**
     * Create a resolver promise
     *
     * @param Field $field
     * @return callable
     */
    public function create(Field $field): callable
    {
        $resolver = $this->resolverFactory->createByClass($field->getResolver());
        return function ($value, $args, $context, $info) use ($resolver, $field) {
            $wrapperInfo = $this->resolveInfoFactory->create($info);
            $this->argumentValidator->validate($field, $args);
            return $resolver->resolve($field, $context, $wrapperInfo, $value, $args);
        };
    }
}
