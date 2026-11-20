<?php
/**
 * Test case for \Magento\Framework\Profiler
 *
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Reflection;

use Magento\Framework\Reflection\Test\Integration\Fixture\NativeDataInterface;
use Magento\TestFramework\Helper\CacheCleaner;

class MethodsMapTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\Reflection\MethodsMap */
    private $object;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->object = $objectManager->create(
            \Magento\Framework\Reflection\MethodsMap::class
        );
    }

    public function testGetMethodsMap()
    {
        $data = $this->object->getMethodsMap(\Magento\Framework\Reflection\MethodsMap::class);
        $this->assertArrayHasKey('getMethodsMap', $data);
        $cachedData = $this->object->getMethodsMap(\Magento\Framework\Reflection\MethodsMap::class);
        $this->assertEquals($data, $cachedData);
    }

    public function testGetMethodParams()
    {
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

    public function testGetMethodsMapForNativeOnlyInterface(): void
    {
        require_once __DIR__ . '/Fixture/NativeDataInterface.php';

        $expected = [
            'getId' => ['type' => 'int', 'isRequired' => true, 'description' => '', 'parameterCount' => 0],
            'getName' => ['type' => 'string', 'isRequired' => false, 'description' => '', 'parameterCount' => 0],
        ];
        self::assertSame($expected, $this->object->getMethodsMap(NativeDataInterface::class));
        $secondInstance = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(MethodsMap::class);
        self::assertSame($expected, $secondInstance->getMethodsMap(NativeDataInterface::class));
    }
}
