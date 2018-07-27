<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Cache\Test\Unit\Frontend\Decorator;

class ProfilerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        \Magento\Framework\Profiler::enable();
    }

    protected function tearDown()
    {
        \Magento\Framework\Profiler::reset();
    }

    /**
     * @param string $method
     * @param array $params
     * @param \Zend_Cache_Backend $cacheBackend
     * @param \Zend_Cache_Core $cacheFrontend
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
        // Cache frontend setup
        $frontendMock = $this->getMock('Magento\Framework\Cache\FrontendInterface');

        $frontendMock->expects($this->any())->method('getBackend')->will($this->returnValue($cacheBackend));

        $frontendMock->expects($this->any())->method('getLowLevelFrontend')->will($this->returnValue($cacheFrontend));

        // Profiler setup
        $driver = $this->getMock('Magento\Framework\Profiler\DriverInterface');
        $driver->expects($this->once())->method('start')->with($expectedProfileId, $expectedProfilerTags);
        $driver->expects($this->once())->method('stop')->with($expectedProfileId);
        \Magento\Framework\Profiler::add($driver);

        // Test
        $object = new \Magento\Framework\Cache\Frontend\Decorator\Profiler($frontendMock, ['Zend_Cache_Backend_']);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ProxyTesting();
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $expectedResult);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function proxyMethodDataProvider()
    {
        $backend = new \Zend_Cache_Backend_BlackHole();
        $adaptee = $this->getMock('Zend_Cache_Core', [], [], '', false);
        $lowLevelFrontend = new \Magento\Framework\Cache\Frontend\Adapter\Zend($adaptee);

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
                    'frontend_type' => 'Magento\Framework\Cache\Frontend\Adapter\Zend',
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
                    'frontend_type' => 'Magento\Framework\Cache\Frontend\Adapter\Zend',
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
                    'frontend_type' => 'Magento\Framework\Cache\Frontend\Adapter\Zend',
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
                    'frontend_type' => 'Magento\Framework\Cache\Frontend\Adapter\Zend',
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
                    'frontend_type' => 'Magento\Framework\Cache\Frontend\Adapter\Zend',
                    'backend_type' => 'BlackHole'
                ],
                true
            ]
        ];
    }
}
