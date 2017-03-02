<?php
/**
 * Test case for \Magento\Framework\Profiler
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection;

use Magento\TestFramework\Helper\CacheCleaner;

class MethodsMapTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Reflection\MethodsMap */
    private $object;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->object = $objectManager->create(
            \Magento\Framework\Reflection\MethodsMap::class
        );
    }

    public function testGetMethodsMap()
    {
        CacheCleaner::cleanAll();
        $data = $this->object->getMethodsMap(\Magento\Framework\Reflection\MethodsMap::class);
        $this->assertArrayHasKey('getMethodsMap', $data);
        $cachedData = $this->object->getMethodsMap(\Magento\Framework\Reflection\MethodsMap::class);
        $this->assertEquals($data, $cachedData);
    }

    public function testGetMethodParams()
    {
        CacheCleaner::cleanAll();
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
}
