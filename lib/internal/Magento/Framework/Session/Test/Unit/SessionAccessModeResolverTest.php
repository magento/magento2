<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Session\Test\Unit;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Session\SessionAccessModeResolver;
use PHPUnit\Framework\TestCase;

class SessionAccessModeResolverTest extends TestCase
{
    public function testReturnsReadOnlyForExplicitlyConfiguredPath(): void
    {
        $request = $this->createMock(Http::class);
        $request->method('getPathInfo')->willReturn('/checkout/cart/add/');

        $resolver = new SessionAccessModeResolver($request, ['checkout/cart/add']);

        self::assertTrue($resolver->isReadOnly());
    }

    public function testDefaultsToWritableForUnconfiguredPath(): void
    {
        $request = $this->createMock(Http::class);
        $request->method('getPathInfo')->willReturn('/checkout/cart/updatePost');

        $resolver = new SessionAccessModeResolver($request, ['checkout/cart/add']);

        self::assertFalse($resolver->isReadOnly());
    }

    public function testDefaultsToWritableWhenRequestDoesNotExposePathInfo(): void
    {
        $resolver = new SessionAccessModeResolver($this->createMock(RequestInterface::class), ['checkout/cart/add']);

        self::assertFalse($resolver->isReadOnly());
    }
}
