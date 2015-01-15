<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System;

require_once realpath(__DIR__ . '/../../../../../../../') . '/tools/Magento/Tools/Migration/System/FileManager.php';
require_once realpath(__DIR__ . '/../../../../../../../') . '/tools/Magento/Tools/Migration/System/FileReader.php';
require_once realpath(
    __DIR__ . '/../../../../../../../'
) . '/tools/Magento/Tools/Migration/System/Writer/Memory.php';
class FileManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Migration\System\FileManager
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_writerMock;

    protected function setUp()
    {
        $this->_readerMock = $this->getMock('Magento\Tools\Migration\System\FileReader', [], [], '', false);
        $this->_writerMock = $this->getMock(
            'Magento\Tools\Migration\System\Writer\Memory',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Tools\Migration\System\FileManager($this->_readerMock, $this->_writerMock);
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_readerMock = null;
        $this->_writerMock = null;
    }

    public function testWrite()
    {
        $this->_writerMock->expects($this->once())->method('write')->with('someFile', 'someContent');
        $this->_model->write('someFile', 'someContent');
    }

    public function testRemove()
    {
        $this->_writerMock->expects($this->once())->method('remove')->with('someFile');
        $this->_model->remove('someFile');
    }

    public function testGetContents()
    {
        $this->_readerMock->expects(
            $this->once()
        )->method(
            'getContents'
        )->with(
            'someFile'
        )->will(
            $this->returnValue('123')
        );
        $this->assertEquals('123', $this->_model->getContents('someFile'));
    }

    public function testGetFileList()
    {
        $expected = ['file1', 'file2'];
        $this->_readerMock->expects(
            $this->once()
        )->method(
            'getFileList'
        )->with(
            'pattern'
        )->will(
            $this->returnValue($expected)
        );

        $this->assertEquals($expected, $this->_model->getFileList('pattern'));
    }
}
