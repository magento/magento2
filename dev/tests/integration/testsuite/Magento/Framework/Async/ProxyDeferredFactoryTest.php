<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Async;

use Magento\Framework\Math\Random;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Testing proxies for deferred values.
 */
class ProxyDeferredFactoryTest extends TestCase
{
    /**
     * @var \TestDeferred\TestClass\ProxyDeferredFactory
     */
    private $factory;

    /**
     * @var CallbackDeferredFactory
     */
    private $callbackDeferredFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->factory = Bootstrap::getObjectManager()->get(ProxyDeferredFactory::class);
        $this->callbackDeferredFactory = Bootstrap::getObjectManager()->get(CallbackDeferredFactory::class);
        $this->random = Bootstrap::getObjectManager()->get(Random::class);
        //phpcs:ignore
        include_once __DIR__ .'/_files/test_class.php';
        \TestDeferred\TestClass::$created = 0;
        $this->factory = Bootstrap::getObjectManager()->get(\TestDeferred\TestClass\ProxyDeferredFactory::class);
    }

    /*
     * Test creating a proxy for deferred value.
     */
    public function testCreate(): void
    {
        $value = $this->random->getRandomString(32);
        $called = 0;
        $callback = function () use ($value, &$called) {
            $called++;
            return new \TestDeferred\TestClass($value);
        };
        /** @var \TestDeferred\TestClass $proxy */
        $proxy = $this->factory->create(
            [
                'deferred' => $this->callbackDeferredFactory->create(['callback' => $callback])
            ]
        );
        $this->assertInstanceOf(\TestDeferred\TestClass::class, $proxy);
        $this->assertEmpty(\TestDeferred\TestClass::$created);
        $this->assertEquals($value, $proxy->getValue());
        $this->assertEquals(1, \TestDeferred\TestClass::$created);
        $this->assertEquals(1, $called);
    }

    /**
     * Test serializing without a value ready.
     */
    public function testSerialize(): void
    {
        $value = $this->random->getRandomString(32);
        $called = 0;
        $callback = function () use ($value, &$called) {
            $called++;
            return new \TestDeferred\TestClass($value);
        };
        /** @var \TestDeferred\TestClass $proxy */
        $proxy = $this->factory->create(
            [
                'deferred' => $this->callbackDeferredFactory->create(['callback' => $callback])
            ]
        );
        //phpcs:disable
        /** @var \TestDeferred\TestClass $unserialized */
        $unserialized = unserialize(serialize($proxy));
        //phpcs:enable
        $this->assertEquals($value, $unserialized->getValue());
        $this->assertEquals($value, $proxy->getValue());
        $this->assertEquals(1, \TestDeferred\TestClass::$created);
        $this->assertEquals(1, $called);
    }

    /**
     * Test cloning the deferred value.
     */
    public function testClone(): void
    {
        $value = $this->random->getRandomString(32);
        $called = 0;
        $callback = function () use ($value, &$called) {
            $called++;
            return new \TestDeferred\TestClass($value);
        };
        /** @var \TestDeferred\TestClass $proxy */
        $proxy = $this->factory->create(
            [
                'deferred' => $this->callbackDeferredFactory->create(['callback' => $callback])
            ]
        );
        $this->assertEquals(0, \TestDeferred\TestClass::$created);
        $this->assertEquals(0, $called);
        $cloned = clone $proxy;
        $this->assertEquals(1, \TestDeferred\TestClass::$created);
        $this->assertEquals(1, $called);
        $this->assertTrue($cloned->isCloned());
        $this->assertFalse($proxy->isCloned());
        $this->assertEquals($value, $cloned->getValue());
    }

    /**
     * Test with deferred value having different type.
     **/
    public function testCreateWrongValue(): void
    {
        $this->expectExceptionMessage("Wrong instance returned by deferred");
        $this->expectException(\RuntimeException::class);
        $callback = function () {
            return new class {
                public function getValue()
                {
                    return 'test';
                }
            };
        };
        /** @var \TestDeferred\TestClass $proxy */
        $proxy = $this->factory->create(
            [
                'deferred' => $this->callbackDeferredFactory->create(['callback' => $callback])
            ]
        );
        $this->assertInstanceOf(\TestDeferred\TestClass::class, $proxy);
        $this->assertEmpty(\TestDeferred\TestClass::$created);
        $proxy->getValue();
    }
}
