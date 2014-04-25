<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * \Magento\Framework\Cache\Backend\Decorator\Compression test case
 */
namespace Magento\Framework\Cache\Backend\Decorator;

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
    protected static $_cacheStorage = array();

    protected function setUp()
    {
        $options = array(
            'concrete_backend' => $this->getMock('Zend_Cache_Backend_File'),
            'compression_threshold' => strlen($this->_testString)
        );
        $this->_decorator = new \Magento\Framework\Cache\Backend\Decorator\Compression($options);
    }

    protected function tearDown()
    {
        unset($this->_decorator);
        self::$_cacheStorage = array();
    }

    public function testCompressData()
    {
        $method = new \ReflectionMethod('Magento\Framework\Cache\Backend\Decorator\Compression', '_compressData');
        $method->setAccessible(true);

        $this->assertStringStartsWith('CACHE_COMPRESSION', $method->invoke($this->_decorator, $this->_testString));
    }

    public function testDecompressData()
    {
        $methodCompress = new \ReflectionMethod(
            'Magento\Framework\Cache\Backend\Decorator\Compression',
            '_compressData'
        );
        $methodCompress->setAccessible(true);

        $methodDecompress = new \ReflectionMethod(
            'Magento\Framework\Cache\Backend\Decorator\Compression',
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
            'Magento\Framework\Cache\Backend\Decorator\Compression',
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
            'Magento\Framework\Cache\Backend\Decorator\Compression',
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

        $backend = $this->getMock('Zend_Cache_Backend_File', array('save', 'load'));
        $backend->expects($this->once())->method('save')->will($this->returnCallback(array(__CLASS__, 'mockSave')));

        $backend->expects($this->once())->method('load')->will($this->returnCallback(array(__CLASS__, 'mockLoad')));

        $options = array('concrete_backend' => $backend, 'compression_threshold' => strlen($this->_testString));

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
