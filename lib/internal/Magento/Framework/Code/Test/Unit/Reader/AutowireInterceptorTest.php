<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Code\Test\Unit\Reader;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Framework\ObjectManager\Test\Unit\Factory\Fixture\AutowireReordered\Interceptor;
use PHPUnit\Framework\TestCase;

/**
 * Resolve attributes safely when an Interceptor constructor differs from its parent.
 */
class AutowireInterceptorTest extends TestCase
{
    /**
     * An extra parameter has no matching parent attribute.
     */
    public function testExtraParameterIsNotAutowired(): void
    {
        $constructor = (new ClassReader())->getConstructor(Interceptor::class);

        self::assertSame(['extra', \stdClass::class, false, null, false], $constructor[2]);
    }

    /**
     * Match the attribute by name rather than its position in the parent signature.
     */
    public function testReorderedParametersUseNames(): void
    {
        $constructor = (new ClassReader())->getConstructor(Interceptor::class);

        self::assertSame(['unattributed', \stdClass::class, false, null, false], $constructor[0]);
        self::assertSame(['dependency', \stdClass::class, false, null, false, true], $constructor[1]);
    }
}
