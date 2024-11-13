<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Decorator;

use Magento\Framework\Cache\Frontend\Adapter\Zend;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Profiler;
use Magento\Framework\Profiler\DriverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ProxyTesting;
use PHPUnit\Framework\TestCase;

class ProfilerTest extends TestCase
{
    protected function setUp(): void
    {
        Profiler::enable();
    }

    protected function tearDown(): void
    {
        Profiler::reset();
    }

    /**
     * @param string $method
     * @param array $params
     * @param \Zend_Cache_Backend $cacheBackend
     * @param \Closure $cacheFrontend
     * @param string $expectedProfileId
     * @param array $expectedProfilerTags
     * @param mixed $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod(
        $method,
        $params,
        $cacheBackend,
        $cacheFrontend,
        $expectedProfileId,
        $expectedProfilerTags,
        $expectedResult
    ) {
        $cacheFrontend = $cacheFrontend($this);
        // Cache frontend setup
        $frontendMock = $this->getMockForAbstractClass(FrontendInterface::class);

        $frontendMock->expects($this->any())->method('getBackend')->willReturn($cacheBackend);

        $frontendMock->expects($this->any())->method('getLowLevelFrontend')->willReturn($cacheFrontend);

        // Profiler setup
        $driver = $this->getMockForAbstractClass(DriverInterface::class);
        $driver->expects($this->once())->method('start')->with($expectedProfileId, $expectedProfilerTags);
        $driver->expects($this->once())->method('stop')->with($expectedProfileId);
        Profiler::add($driver);

        // Test
        $object = new \Magento\Framework\Cache\Frontend\Decorator\Profiler($frontendMock, ['Zend_Cache_Backend_']);
        $helper = new ProxyTesting();
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $expectedResult);
        $this->assertSame($expectedResult, $result);
    }

    protected function getMockForZendCache()
    {
        $adaptee = $this->createMock(\Zend_Cache_Core::class);
        $frontendFactory = function () use ($adaptee) {
            return $adaptee;
        };
        $lowLevelFrontend = new Zend($frontendFactory);
        return $lowLevelFrontend;
    }

    /**
     * @return array
     */
    public static function proxyMethodDataProvider()
    {
        $backend = new \Zend_Cache_Backend_BlackHole();
        $lowLevelFrontend = static fn (self $testCase) => $testCase->getMockForZendCache();

        return [
            [
                'test',
                ['record_id'],
                $backend,
                $lowLevelFrontend,
                'cache_test',
                [
                    'group' => 'cache',
                    'operation' => 'cache:test',
                    'frontend_type' => Zend::class,
                    'backend_type' => 'BlackHole'
                ],
                111,
            ],
            [
                'load',
                ['record_id'],
                $backend,
                $lowLevelFrontend,
                'cache_load',
                [
                    'group' => 'cache',
                    'operation' => 'cache:load',
                    'frontend_type' => Zend::class,
                    'backend_type' => 'BlackHole'
                ],
                '111'
            ],
            [
                'save',
                ['record_value', 'record_id', ['tag'], 555],
                $backend,
                $lowLevelFrontend,
                'cache_save',
                [
                    'group' => 'cache',
                    'operation' => 'cache:save',
                    'frontend_type' => Zend::class,
                    'backend_type' => 'BlackHole'
                ],
                true
            ],
            [
                'remove',
                ['record_id'],
                $backend,
                $lowLevelFrontend,
                'cache_remove',
                [
                    'group' => 'cache',
                    'operation' => 'cache:remove',
                    'frontend_type' => Zend::class,
                    'backend_type' => 'BlackHole'
                ],
                true
            ],
            [
                'clean',
                [\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, ['tag']],
                $backend,
                $lowLevelFrontend,
                'cache_clean',
                [
                    'group' => 'cache',
                    'operation' => 'cache:clean',
                    'frontend_type' => Zend::class,
                    'backend_type' => 'BlackHole'
                ],
                true
            ]
        ];
    }
}
