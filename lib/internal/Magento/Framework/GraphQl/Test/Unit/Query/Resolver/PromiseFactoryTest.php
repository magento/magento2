<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Query\Resolver;

use GraphQL\Type\Definition\ResolveInfo as ResolveInfoDefinition;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Argument\ValidatorInterface;
use Magento\Framework\GraphQl\Query\Resolver\Factory as ResolverFactory;
use Magento\Framework\GraphQl\Query\Resolver\PromiseFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfoFactory;
use PHPUnit\Framework\TestCase;

/**
 * Verify behavior of resolver promise factory
 */
class PromiseFactoryTest extends TestCase
{
    public function testCreatesPromise()
    {
        $resolver = self::getMockBuilder(ResolverInterface::class)
            ->getMock();
        $infoDefinition = self::getMockBuilder(ResolveInfoDefinition::class)
            ->disableOriginalConstructor()
            ->getMock();
        $info = self::getMockBuilder(ResolveInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var Field $field */
        $field = self::getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ResolverFactory $resolverFactory */
        $resolverFactory = self::getMockBuilder(ResolverFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resolverFactory->method('createByClass')
            ->willReturn($resolver);

        /** @var ResolveInfoFactory $resolveInfoFactory */
        $resolveInfoFactory = self::getMockBuilder(ResolveInfoFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resolveInfoFactory->expects(self::once())
            ->method('create')
            ->with($infoDefinition)
            ->willReturn($info);

        /** @var ValidatorInterface $argumentValidator */
        $argumentValidator = self::getMockBuilder(ValidatorInterface::class)
            ->getMock();

        $argumentValidator->expects(self::once())
            ->method('validate')
            ->with($field, ['my_args']);

        $resolver->expects(self::once())
            ->method('resolve')
            ->with($field, 'my_context', $info, ['my_value'], ['my_args'])
            ->willReturn('abc');

        $factory = new PromiseFactory(
            $resolverFactory,
            $resolveInfoFactory,
            $argumentValidator
        );

        $promise = $factory->create($field);
        $result = $promise(['my_value'], ['my_args'], 'my_context', $infoDefinition);

        self::assertSame('abc', $result);
    }
}
