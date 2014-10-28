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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\Migration\System;


require_once realpath(__DIR__ . '/../../../../../../../../') . '/tools/Magento/Tools/Migration/System/FileManager.php';
require_once realpath(__DIR__ . '/../../../../../../../../') . '/tools/Magento/Tools/Migration/System/FileReader.php';
require_once realpath(
    __DIR__ . '/../../../../../../../../'
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
        $this->_readerMock = $this->getMock('Magento\Tools\Migration\System\FileReader', array(), array(), '', false);
        $this->_writerMock = $this->getMock(
            'Magento\Tools\Migration\System\Writer\Memory',
            array(),
            array(),
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
        $expected = array('file1', 'file2');
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
