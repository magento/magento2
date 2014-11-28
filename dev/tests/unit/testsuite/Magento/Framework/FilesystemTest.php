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

use Magento\Framework\App\Filesystem\DirectoryList;

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
        $this->_filesystem = new Filesystem(
            $this->_directoryListMock,
            $this->_dirReadFactoryMock,
            $this->_dirWriteFactoryMock
        );
    }

    public function testGetDirectoryRead()
    {
        /** @var \Magento\Framework\Filesystem\Directory\ReadInterface $dirReadMock */
        $dirReadMock = $this->getMock('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->_dirReadFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirReadMock));
        $this->assertEquals($dirReadMock, $this->_filesystem->getDirectoryRead(DirectoryList::ROOT));
    }

    public function testGetDirectoryWrite()
    {
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $dirWriteMock */
        $dirWriteMock = $this->getMock('Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->_dirWriteFactoryMock->expects($this->once())->method('create')->will($this->returnValue($dirWriteMock));
        $this->assertEquals($dirWriteMock, $this->_filesystem->getDirectoryWrite(DirectoryList::ROOT));
    }

    public function testGetUri()
    {
        $this->_directoryListMock->expects($this->once())->method('getUrlPath')->with('code')->willReturn('result');
        $this->assertEquals('result', $this->_filesystem->getUri('code'));
    }
}
