<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Decorator;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Frontend\Adapter\Symfony;
use Magento\Framework\Cache\Frontend\Adapter\Zend;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Profiler;
use Magento\Framework\Profiler\DriverInterface;
use Magento\Framework\TestFramework\Unit\Helper\ProxyTesting;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;

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
     * @param mixed $cacheBackend
     * @param \Closure $cacheFrontend
     * @param string $expectedProfileId
     * @param array $expectedProfilerTags
     * @param mixed $expectedResult
     */
    #[DataProvider('proxyMethodDataProvider')]
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
        $frontendMock = $this->createMock(FrontendInterface::class);

        $frontendMock->expects($this->any())->method('getBackend')->willReturn($cacheBackend);

        $frontendMock->expects($this->any())->method('getLowLevelFrontend')->willReturn($cacheFrontend);

        // Profiler setup
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('start')->with($expectedProfileId, $expectedProfilerTags);
        $driver->expects($this->once())->method('stop')->with($expectedProfileId);
        Profiler::add($driver);

        // Test
        $object = new \Magento\Framework\Cache\Frontend\Decorator\Profiler($frontendMock, []);
        $helper = new ProxyTesting();
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $expectedResult);
        $this->assertSame($expectedResult, $result);
    }

    protected function getMockForZendCache()
    {
        $adaptee = $this->createMock(\Psr\Cache\CacheItemPoolInterface::class);
        $frontendFactory = function () use ($adaptee) {
            return $adaptee;
        };
        $lowLevelFrontend = new Symfony($frontendFactory);
        return $lowLevelFrontend;
    }

    /**
     * @return array
     */
    public static function proxyMethodDataProvider()
    {
        $backend = new NullAdapter();
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
                    'frontend_type' => Symfony::class,
                    'backend_type' => NullAdapter::class
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
                    'frontend_type' => Symfony::class,
                    'backend_type' => NullAdapter::class
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
                    'frontend_type' => Symfony::class,
                    'backend_type' => NullAdapter::class
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
                    'frontend_type' => Symfony::class,
                    'backend_type' => NullAdapter::class
                ],
                true
            ],
            [
                'clean',
                [CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG, ['tag']],
                $backend,
                $lowLevelFrontend,
                'cache_clean',
                [
                    'group' => 'cache',
                    'operation' => 'cache:clean',
                    'frontend_type' => Symfony::class,
                    'backend_type' => NullAdapter::class
                ],
                true
            ]
        ];
    }
}
