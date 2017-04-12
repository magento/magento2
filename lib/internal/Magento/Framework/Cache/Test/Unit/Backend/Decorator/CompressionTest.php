<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * \Magento\Framework\Cache\Backend\Decorator\Compression test case
 */
namespace Magento\Framework\Cache\Test\Unit\Backend\Decorator;

class CompressionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Cache\Backend\Decorator\Compression
     */
    protected $_decorator;

    /**
     * @var string
     */
    protected $_testString = 'Any string';

    /**
     * @var array
     */
    protected static $_cacheStorage = [];

    protected function setUp()
    {
        $options = [
            'concrete_backend' => $this->getMock(\Zend_Cache_Backend_File::class),
            'compression_threshold' => strlen($this->_testString),
        ];
        $this->_decorator = new \Magento\Framework\Cache\Backend\Decorator\Compression($options);
    }

    protected function tearDown()
    {
        unset($this->_decorator);
        self::$_cacheStorage = [];
    }

    public function testCompressData()
    {
        $method = new \ReflectionMethod(
            \Magento\Framework\Cache\Backend\Decorator\Compression::class,
            '_compressData'
        );
        $method->setAccessible(true);

        $this->assertStringStartsWith('CACHE_COMPRESSION', $method->invoke($this->_decorator, $this->_testString));
    }

    public function testDecompressData()
    {
        $methodCompress = new \ReflectionMethod(
            \Magento\Framework\Cache\Backend\Decorator\Compression::class,
            '_compressData'
        );
        $methodCompress->setAccessible(true);

        $methodDecompress = new \ReflectionMethod(
            \Magento\Framework\Cache\Backend\Decorator\Compression::class,
            '_decompressData'
        );
        $methodDecompress->setAccessible(true);

        $this->assertEquals(
            $this->_testString,
            $methodDecompress->invoke(
                $this->_decorator,
                $methodCompress->invoke($this->_decorator, $this->_testString)
            )
        );
    }

    public function testIsCompressionNeeded()
    {
        $method = new \ReflectionMethod(
            \Magento\Framework\Cache\Backend\Decorator\Compression::class,
            '_isCompressionNeeded'
        );
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($this->_decorator, $this->_testString));
        $this->assertFalse($method->invoke($this->_decorator, substr($this->_testString, 0, -1)));
        $this->assertTrue($method->invoke($this->_decorator, $this->_testString . 's'));
    }

    public function testIsDecompressionNeeded()
    {
        $prefix = 'CACHE_COMPRESSION';

        $method = new \ReflectionMethod(
            \Magento\Framework\Cache\Backend\Decorator\Compression::class,
            '_isDecompressionNeeded'
        );
        $method->setAccessible(true);

        $this->assertFalse($method->invoke($this->_decorator, $this->_testString));
        $this->assertFalse($method->invoke($this->_decorator, 's' . $prefix . $this->_testString));
        $this->assertTrue($method->invoke($this->_decorator, $prefix . $this->_testString));
    }

    public function testSaveLoad()
    {
        $cacheId = 'cacheId' . rand(1, 100);

        $backend = $this->getMock(\Zend_Cache_Backend_File::class, ['save', 'load']);
        $backend->expects($this->once())->method('save')->will($this->returnCallback([__CLASS__, 'mockSave']));

        $backend->expects($this->once())->method('load')->will($this->returnCallback([__CLASS__, 'mockLoad']));

        $options = ['concrete_backend' => $backend, 'compression_threshold' => strlen($this->_testString)];

        $decorator = new \Magento\Framework\Cache\Backend\Decorator\Compression($options);

        $decorator->setOption('write_control', false);
        $decorator->setOption('automatic_cleaning_factor', 0);

        $decorator->save($this->_testString, $cacheId);

        $this->assertArrayHasKey($cacheId, self::$_cacheStorage);
        $this->assertInternalType('string', self::$_cacheStorage[$cacheId]);

        $loadedValue = $decorator->load($cacheId);

        $this->assertEquals($this->_testString, $loadedValue);
    }

    public static function mockSave($data, $cacheId)
    {
        self::$_cacheStorage[$cacheId] = $data;
        return true;
    }

    public static function mockLoad($cacheId)
    {
        return array_key_exists($cacheId, self::$_cacheStorage) ? self::$_cacheStorage[$cacheId] : false;
    }
}
