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
namespace Magento\Framework;

use Magento\Framework\App\Filesystem as AppFilesystem;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /** @var Filesystem */
    protected $_filesystem;

    /** @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dirReadFactoryMock;

    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $_dirWriteFactoryMock;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList|\PHPUnit_Framework_MockObject_MockObject  */
    protected $_directoryListMock;

    /** @var \Magento\Framework\Filesystem\File\ReadFactory|\PHPUnit_Framework_MockObject_MockObject  */
    protected $_fileReadFactoryMock;

    public function setUp()
    {
        $this->_dirReadFactoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\ReadFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_directoryListMock = $this->getMock(
            'Magento\Framework\App\Filesystem\DirectoryList',
            array(),
            array(),
            '',
            false
        );
        $this->_dirWriteFactoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\WriteFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_fileReadFactoryMock = $this->getMock(
            'Magento\Framework\Filesystem\File\ReadFactory',
            array(),
            array(),
            '',
            false
        );

        $this->_filesystem = new Filesystem(
            $this->_directoryListMock,
            $this->_dirReadFactoryMock,
            $this->_dirWriteFactoryMock,
            $this->_fileReadFactoryMock
        );
    }

    public function testGetDirectoryRead()
    {
        $this->_setupDirectoryListMock(array());
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $dirReadMock */
        $dirReadMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->_dirReadFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirReadMock));
        $this->assertEquals($dirReadMock, $this->_filesystem->getDirectoryRead(AppFilesystem::ROOT_DIR));
    }

    /**
     * @expectedException \Magento\Framework\Filesystem\FilesystemException
     */
    public function testGetDirectoryWriteReadOnly()
    {
        $this->_setupDirectoryListMock(array('read_only' => true));
        $this->_filesystem->getDirectoryWrite(AppFilesystem::ROOT_DIR);
    }

    public function testGetDirectoryWrite()
    {
        $this->_setupDirectoryListMock(array());
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $dirWriteMock */
        $dirWriteMock = $this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->_dirWriteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirWriteMock));
        $this->assertEquals($dirWriteMock, $this->_filesystem->getDirectoryWrite(AppFilesystem::ROOT_DIR));
    }

    public function testGetRemoteResource()
    {
        $fileReadMock = $this->getMock('Magento\Framework\Filesystem\File\ReadInterface', array(), array(), '', false);

        $this->_fileReadFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'example.com',
            'http'
        )->will(
            $this->returnValue($fileReadMock)
        );

        $this->assertEquals($fileReadMock, $this->_filesystem->getRemoteResource('http://example.com'));
    }

    public function testGetUri()
    {
        $uri = 'http://example.com';
        $this->_setupDirectoryListMock(array('uri' => $uri));
        $this->assertEquals($uri, $this->_filesystem->getUri(AppFilesystem::ROOT_DIR));
    }

    protected function _setupDirectoryListMock(array $config)
    {
        $this->_directoryListMock->expects($this->any())->method('getConfig')->will($this->returnValue($config));
    }
}
