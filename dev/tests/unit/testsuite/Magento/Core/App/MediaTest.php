<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\App;

use Magento\Framework\App\Filesystem\DirectoryList;

class MediaTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\App\Media
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var callable
     */
    protected $_closure;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_sync;

    /**
     * @var string
     */
    protected $_mediaDirectory = 'mediaDirectory';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_responseMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Read|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryReadMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock('Magento\Core\Model\File\Storage\Request', [], [], '', false);
        $this->_closure = function () {
            return true;
        };
        $this->_objectManagerMock = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_configMock = $this->getMock('Magento\Core\Model\File\Storage\Config', [], [], '', false);
        $this->_sync = $this->getMock('Magento\Core\Model\File\Storage\Synchronization', [], [], '', false);

        $this->filesystemMock = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->directoryReadMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Read',
            [],
            [],
            '',
            false
        );

        $this->filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryRead'
        )->with(
            DirectoryList::MEDIA
        )->will(
            $this->returnValue($this->directoryReadMock)
        );

        $this->_responseMock = $this->getMock('Magento\Core\Model\File\Storage\Response', [], [], '', false);

        $map = [
            ['Magento\Core\Model\File\Storage\Request', $this->_requestMock],
            ['Magento\Core\Model\File\Storage\Synchronization', $this->_sync],
        ];
        $this->_model = new \Magento\Core\App\Media(
            $this->_objectManagerMock,
            $this->_requestMock,
            $this->_responseMock,
            $this->_closure,
            'baseDir',
            'mediaDirectory',
            'var',
            'params',
            $this->filesystemMock
        );
        $this->_objectManagerMock->expects($this->any())->method('get')->will($this->returnValueMap($map));
    }

    protected function tearDown()
    {
        unset($this->_model);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The specified path is not within media directory.
     */
    public function testProcessRequestCreatesConfigFileMediaDirectoryIsNotProvided()
    {
        $this->_model = new \Magento\Core\App\Media(
            $this->_objectManagerMock,
            $this->_requestMock,
            $this->_responseMock,
            $this->_closure,
            'baseDir',
            false,
            'var',
            'params',
            $this->filesystemMock
        );
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Core\Model\File\Storage\Config'
        )->will(
            $this->returnValue($this->_configMock)
        );
        $this->_configMock->expects($this->once())->method('save');
        $this->_model->launch();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The specified path is not allowed.
     */
    public function testProcessRequestReturnsNotFoundResponseIfResourceIsNotAllowed()
    {
        $this->_closure = function () {
            return false;
        };
        $this->_model = new \Magento\Core\App\Media(
            $this->_objectManagerMock,
            $this->_requestMock,
            $this->_responseMock,
            $this->_closure,
            'baseDir',
            false,
            'var',
            'params',
            $this->filesystemMock
        );
        $this->_requestMock->expects($this->once())->method('getPathInfo');
        $this->_objectManagerMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'Magento\Core\Model\File\Storage\Config'
        )->will(
            $this->returnValue($this->_configMock)
        );
        $this->_configMock->expects($this->once())->method('getAllowedResources')->will($this->returnValue(false));
        $this->_model->launch();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage The specified path is not within media directory.
     */
    public function testProcessRequestReturnsNotFoundIfFileIsNotAllowed()
    {
        $this->_configMock->expects($this->never())->method('save');
        $this->_requestMock->expects($this->once())->method('getPathInfo');
        $this->_requestMock->expects($this->never())->method('getFilePath');
        $this->_model->launch();
    }

    public function testProcessRequestReturnsFileIfItsProperlySynchronized()
    {
        $relativeFilePath = '_files';
        $filePath = str_replace('\\', '/', __DIR__ . '/' . $relativeFilePath);
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getPathInfo'
        )->will(
            $this->returnValue($this->_mediaDirectory . '/')
        );
        $this->_sync->expects($this->once())->method('synchronize');
        $this->_requestMock->expects($this->any())->method('getFilePath')->will($this->returnValue($filePath));

        $this->directoryReadMock->expects(
            $this->once()
        )->method(
            'getRelativePath'
        )->with(
            $filePath
        )->will(
            $this->returnValue($relativeFilePath)
        );

        $this->directoryReadMock->expects(
            $this->once()
        )->method(
            'isReadable'
        )->with(
            $relativeFilePath
        )->will(
            $this->returnValue(true)
        );
        $this->_responseMock->expects($this->once())->method('setFilePath')->with($filePath);
        $this->assertSame($this->_responseMock, $this->_model->launch());
    }

    public function testProcessRequestReturnsNotFoundIfFileIsNotSynchronized()
    {
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getPathInfo'
        )->will(
            $this->returnValue($this->_mediaDirectory . '/')
        );
        $this->_sync->expects($this->once())->method('synchronize');
        $this->_requestMock->expects(
            $this->any()
        )->method(
            'getFilePath'
        )->will(
            $this->returnValue('non_existing_file_name')
        );
        $this->_responseMock->expects($this->once())->method('setHttpResponseCode')->with(404);
        $this->assertSame($this->_responseMock, $this->_model->launch());
    }
}
