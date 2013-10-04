<?php
/**
 * Test for \Magento\Filesystem\Stream\Local
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Stream;

class LocalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Filesystem\Stream\Local
     */
    protected $_stream;

    /**
     * @var string
     */
    protected $_writeFileName;

    /**
     * @var string
     */
    protected $_openedFile;

    protected function setUp()
    {
        $this->_openedFile = __DIR__ . DS . '..' . DS . '_files' . DS . 'popup.csv';
        $this->_stream = new \Magento\Filesystem\Stream\Local($this->_openedFile);
        $this->_writeFileName = __DIR__ . DS . '..' . DS . '_files' . DS . 'new.css';
    }

    protected function tearDown()
    {
        if (file_exists($this->_writeFileName)) {
            unlink($this->_writeFileName);
        }
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     */
    public function testOpenException()
    {
        $stream = new \Magento\Filesystem\Stream\Local(__DIR__ . DS . '..' . DS . '_files' . DS . 'invalid.css');
        $stream->open(new \Magento\Filesystem\Stream\Mode('r'));
    }

    public function testOpenNewFile()
    {
        $stream = new \Magento\Filesystem\Stream\Local($this->_writeFileName);
        $stream->open(new \Magento\Filesystem\Stream\Mode('w'));
    }

    public function testOpenExistingFile()
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
    }

    public function testRead()
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $data  = $this->_stream->read(15);
        $this->assertEquals('var myData = 5;', $data);

    }

    public function testReadCsv()
    {
        $stream = new \Magento\Filesystem\Stream\Local(__DIR__ . DS . '..' . DS . '_files' . DS . 'data.csv');
        $stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $data = $stream->readCsv(0);
        $this->assertEquals(array('field1', 'field2'), $data);
        $data = $stream->readCsv(0);
        $this->assertEquals(array('field3', 'field4'), $data);
        $data = $stream->readCsv(0);
        $this->assertFalse($data);
    }

    public function testWrite()
    {
        $stream = new \Magento\Filesystem\Stream\Local($this->_writeFileName);
        $stream->open(new \Magento\Filesystem\Stream\Mode('w'));
        $stream->write('test data');
        $this->assertEquals('test data', file_get_contents($this->_writeFileName));
    }

    public function testWriteCsv()
    {
        $stream = new \Magento\Filesystem\Stream\Local($this->_writeFileName);
        $stream->open(new \Magento\Filesystem\Stream\Mode('w'));
        $stream->writeCsv(array('data1', 'data2'));
        $stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $this->assertEquals(array('data1', 'data2'), $stream->readCsv());
    }

    public function testClose()
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $this->_stream->lock();
        $this->assertAttributeEquals(true, '_isLocked', $this->_stream);
        $this->_stream->close();
        $this->assertAttributeEquals(false, '_isLocked', $this->_stream);
        $this->assertAttributeEquals(null, '_mode', $this->_stream);
        $this->assertAttributeEquals(null, '_fileHandle', $this->_stream);
    }

    public function testLock()
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $this->_stream->lock(true);
        $this->assertAttributeEquals(true, '_isLocked', $this->_stream);
        $this->_stream->unlock();
        $this->assertAttributeEquals(false, '_isLocked', $this->_stream);
    }

    public function testFlush()
    {
        $stream = new \Magento\Filesystem\Stream\Local($this->_writeFileName);
        $stream->open(new \Magento\Filesystem\Stream\Mode('w'));
        $stream->write('test data');
        $stream->flush();
        $this->assertEquals('test data', file_get_contents($this->_writeFileName));
    }

    public function testSeek()
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $this->_stream->seek(14);
        $this->assertEquals(';', $this->_stream->read(1));
    }

    /**
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage seek operation on the stream caused an error.
     */
    public function testSeekError()
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $this->_stream->seek(-1);
    }

    public function testTell()
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $this->assertEquals(0, $this->_stream->tell());
        $this->_stream->seek(14);
        $this->assertEquals(14, $this->_stream->tell());
    }

    public function testEof()
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        $this->assertFalse($this->_stream->eof());
        $this->_stream->read(15);
        $this->_stream->read(15);
        $this->assertTrue($this->_stream->eof());
    }

    /**
     * @param string $method
     * @param array $arguments
     * @dataProvider streamNotOpenedDataProvider
     * @expectedException \Magento\Filesystem\FilesystemException
     */
    public function testExceptionStreamNotOpened($method, array $arguments = array(1))
    {
        call_user_func(array($this->_stream, $method), $arguments);
    }

    /**
     * @return array
     */
    public function streamNotOpenedDataProvider()
    {
        return array(
            array('read'),
            array('readCsv'),
            array('write'),
            array('writeCsv', array(array(1))),
            array('close'),
            array('flush'),
            array('seek'),
            array('tell'),
            array('tell'),
            array('eof'),
        );
    }

    /**
     * @param string $method
     * @dataProvider forbiddenReadDataProvider
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage The stream does not allow read.
     */
    public function testForbiddenRead($method)
    {
        $stream = new \Magento\Filesystem\Stream\Local($this->_writeFileName);
        $stream->open(new \Magento\Filesystem\Stream\Mode('w'));
        $stream->$method(1);
    }

    /**
     * @return array
     */
    public function forbiddenReadDataProvider()
    {
        return array(
            array('read'),
            array('readCsv'),
        );
    }

    /**
     * @param string $method
     * @param array $arguments
     * @dataProvider forbiddenWriteDataProvider
     * @expectedException \Magento\Filesystem\FilesystemException
     * @expectedExceptionMessage The stream does not allow write.
     */
    public function testForbiddenWrite($method, array $arguments = array(1))
    {
        $this->_stream->open(new \Magento\Filesystem\Stream\Mode('r'));
        call_user_func(array($this->_stream, $method), $arguments);
    }

    /**
     * @return array
     */
    public function forbiddenWriteDataProvider()
    {
        return array(
            array('write'),
            array('writeCsv', array(array(1))),
        );
    }
}
