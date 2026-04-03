<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * \Magento\Framework\Cache\Backend\Decorator\Compression test case
 *
 * @deprecated Tests deprecated class Compression
 * @see \Magento\Framework\Cache\Backend\Decorator\Compression
 * @group legacy
 * @group disabled
 */
namespace Magento\Framework\Cache\Test\Unit\Backend\Decorator;

use Magento\Framework\Cache\Backend\Decorator\Compression;
use PHPUnit\Framework\TestCase;

class CompressionTest extends TestCase
{
    /**
     * @var Compression
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

    /**
     * Skip all tests as the class being tested is deprecated
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->markTestSkipped(
            'Test skipped: Compression is deprecated. Compression is handled by backend libraries directly.'
        );
    }

    protected function tearDown(): void
    {
        unset($this->_decorator);
        self::$_cacheStorage = [];
    }

    public function testCompressData()
    {
        $method = new \ReflectionMethod(
            Compression::class,
            '_compressData'
        );

        $this->assertStringStartsWith('CACHE_COMPRESSION', $method->invoke($this->_decorator, $this->_testString));
    }

    public function testDecompressData()
    {
        $methodCompress = new \ReflectionMethod(
            Compression::class,
            '_compressData'
        );

        $methodDecompress = new \ReflectionMethod(
            Compression::class,
            '_decompressData'
        );

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
            Compression::class,
            '_isCompressionNeeded'
        );
        $this->assertFalse($method->invoke($this->_decorator, $this->_testString));
        $this->assertFalse($method->invoke($this->_decorator, substr($this->_testString, 0, -1)));
        $this->assertTrue($method->invoke($this->_decorator, $this->_testString . 's'));
    }

    public function testIsDecompressionNeeded()
    {
        $prefix = 'CACHE_COMPRESSION';

        $method = new \ReflectionMethod(
            Compression::class,
            '_isDecompressionNeeded'
        );

        $this->assertFalse($method->invoke($this->_decorator, $this->_testString));
        $this->assertFalse($method->invoke($this->_decorator, 's' . $prefix . $this->_testString));
        $this->assertTrue($method->invoke($this->_decorator, $prefix . $this->_testString));
    }

    public function testSaveLoad()
    {
        $cacheId = 'cacheId' . rand(1, 100);

        $backend = $this->createPartialMock(\Zend_Cache_Backend_File::class, ['save', 'load']);
        $backend->expects($this->once())->method('save')->willReturnCallback([__CLASS__, 'mockSave']);

        $backend->expects($this->once())->method('load')->willReturnCallback([__CLASS__, 'mockLoad']);

        $options = ['concrete_backend' => $backend, 'compression_threshold' => strlen($this->_testString)];

        $decorator = new Compression($options);

        $decorator->setOption('write_control', false);
        $decorator->setOption('automatic_cleaning_factor', 0);

        $decorator->save($this->_testString, $cacheId);

        $this->assertArrayHasKey($cacheId, self::$_cacheStorage);
        $this->assertIsString(self::$_cacheStorage[$cacheId]);

        $loadedValue = $decorator->load($cacheId);

        $this->assertEquals($this->_testString, $loadedValue);
    }

    /**
     * @param $data
     * @param $cacheId
     * @return bool
     */
    public static function mockSave($data, $cacheId)
    {
        self::$_cacheStorage[$cacheId] = $data;
        return true;
    }

    /**
     * @param $cacheId
     * @return bool|mixed
     */
    public static function mockLoad($cacheId)
    {
        return array_key_exists($cacheId, self::$_cacheStorage) ? self::$_cacheStorage[$cacheId] : false;
    }
}
