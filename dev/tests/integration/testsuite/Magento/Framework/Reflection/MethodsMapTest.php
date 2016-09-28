<?php
/**
 * Test case for \Magento\Framework\Profiler
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection;

class MethodsMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Reflection\MethodsMap */
    private $object;

    /** @var \Magento\TestFramework\ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->object = $this->objectManager->create(
            \Magento\Framework\Reflection\MethodsMap::class
        );
    }

    public function testGetMethodsMap()
    {
        $this->cleanAllCache();
        $data = $this->object->getMethodsMap(\Magento\Framework\Reflection\MethodsMap::class);
        $this->assertArrayHasKey('getMethodsMap', $data);
        $cachedData = $this->object->getMethodsMap(\Magento\Framework\Reflection\MethodsMap::class);
        $this->assertEquals($data, $cachedData);
    }

    public function testGetMethodParams()
    {
        $this->cleanAllCache();
        $data = $this->object->getMethodParams(
            \Magento\Framework\Reflection\MethodsMap::class,
            'getMethodParams'
        );
        $this->assertCount(2, $data);
        $cachedData = $this->object->getMethodParams(
            \Magento\Framework\Reflection\MethodsMap::class,
            'getMethodParams'
        );
        $this->assertEquals($data, $cachedData);
    }

    private function cleanAllCache()
    {
        /** @var \Magento\Framework\App\Cache\Frontend\Pool $cachePool */
        $cachePool = $this->objectManager->get(\Magento\Framework\App\Cache\Frontend\Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }
    }
}
