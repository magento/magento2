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
namespace Magento\View;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class PublisherTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\View\Publisher */
    protected $publisher;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    /** @var \Magento\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $viewFileSystem;

    /** @var \Magento\View\Asset\PreProcessor\PreProcessorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $preProcessorMock;

    /** @var \Magento\View\Publisher\FileFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileFactoryMock;

    /** @var \Magento\View\Publisher\FileInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $publisherFileMock;

    /** @var \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $rootDirectory;

    /** @var \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $tmpDirectory;

    /** @var \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $pubDirectory;

    protected function setUp()
    {
        $this->rootDirectory = $this->getMock(
            'Magento\Filesystem\Directory\WriteInterface',
            array(),
            array(),
            '',
            false
        );
        $this->tmpDirectory = $this->getMock(
            'Magento\Filesystem\Directory\WriteInterface',
            array(),
            array(),
            '',
            false
        );
        $this->pubDirectory = $this->getMock(
            'Magento\Filesystem\Directory\WriteInterface',
            array(),
            array(),
            '',
            false
        );

        $this->filesystemMock = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $this->filesystemMock->expects(
            $this->any()
        )->method(
            'getDirectoryWrite'
        )->will(
            $this->returnCallback(array($this, 'getDirectoryWriteCallback'))
        );

        $this->viewFileSystem = $this->getMock('Magento\View\FileSystem', array(), array(), '', false);
        $this->preProcessorMock = $this->getMock('Magento\View\Asset\PreProcessor\PreProcessorInterface');
        $this->fileFactoryMock = $this->getMock('Magento\View\Publisher\FileFactory', array(), array(), '', false);
        $this->publisherFileMock = $this->getMock('Magento\View\Publisher\FileInterface', array(), array(), '', false);
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->publisher = $this->objectManagerHelper->getObject(
            'Magento\View\Publisher',
            array(
                'filesystem' => $this->filesystemMock,
                'viewFileSystem' => $this->viewFileSystem,
                'preProcessor' => $this->preProcessorMock,
                'fileFactory' => $this->fileFactoryMock
            )
        );
    }

    /**
     * @param string $param
     * @return \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     * @throws \UnexpectedValueException
     */
    public function getDirectoryWriteCallback($param)
    {
        switch ($param) {
            case \Magento\App\Filesystem::ROOT_DIR:
                return $this->rootDirectory;
            case \Magento\App\Filesystem::VAR_DIR:
                return $this->tmpDirectory;
            case \Magento\App\Filesystem::STATIC_VIEW_DIR:
                return $this->pubDirectory;
            default:
                throw new \UnexpectedValueException('Directory write callback received wrong value: ' . $param);
        }
    }

    public function testGetPublicFilePathNotAllowedExtension()
    {
        $filePath = 'some/file/path.php';
        $params = array('some', 'array');

        $this->publisherFileMock->expects($this->once())->method('getExtension')->will($this->returnValue('php'));

        $this->fileFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($filePath),
            $this->equalTo($params)
        )->will(
            $this->returnValue($this->publisherFileMock)
        );

        $this->assertSame(null, $this->publisher->getPublicFilePath($filePath, $params));
    }

    /**
     * @expectedException \Magento\Exception
     * @expectedExceptionMessage Unable to locate theme file 'some/file/path'.
     */
    public function testGetPublicFilePathException()
    {
        $filePath = 'some/file/path';
        $params = array('some', 'array2');

        $this->publisherFileMock->expects($this->once())->method('getExtension')->will($this->returnValue('css'));

        $this->publisherFileMock->expects(
            $this->once()
        )->method(
            'isSourceFileExists'
        )->will(
            $this->returnValue(false)
        );

        $this->publisherFileMock->expects($this->once())->method('getFilePath')->will($this->returnValue($filePath));

        $this->prepareCommonMocks($filePath, $params);

        $this->publisher->getPublicFilePath($filePath, $params);
    }

    public function testGetPublicFilePathPublicationNotAllowed()
    {
        $filePath = 'some/file/path.css';
        $params = array('some', 'array3');

        $this->publisherFileMock->expects($this->once())->method('getExtension')->will($this->returnValue('css'));

        $this->publisherFileMock->expects($this->once())->method('isSourceFileExists')->will($this->returnValue(true));

        $this->publisherFileMock->expects(
            $this->once()
        )->method(
            'isPublicationAllowed'
        )->will(
            $this->returnValue(false)
        );

        $this->publisherFileMock->expects(
            $this->once()
        )->method(
            'getSourcePath'
        )->will(
            $this->returnValue('some/source/path.css')
        );

        $this->prepareCommonMocks($filePath, $params);

        $this->assertSame('some/source/path.css', $this->publisher->getPublicFilePath($filePath, $params));
    }

    /**
     * @param string[] $testConfig
     * @return void
     * @dataProvider getPublicFilePathDataProvider
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testGetPublicFilePath($testConfig)
    {
        $filePath = 'some/file/path.css';
        $params = array('some', 'array4');
        $sourcePath = 'some/source/path.css';
        $result = $testConfig['result'];
        $timeSource = $testConfig['timeSource'];
        $timeTarget = $testConfig['timeTarget'];
        $isExistsTarget = $testConfig['isExistsTarget'];
        $shouldBeUpdated = $testConfig['shouldBeUpdated'];
        $isFile = $testConfig['isFile'];
        $isDirectory = $testConfig['isDirectory'];

        $this->prepareCommonMocks($filePath, $params);

        $this->publisherFileMock->expects($this->once())->method('getExtension')->will($this->returnValue('css'));

        $this->publisherFileMock->expects($this->once())->method('isSourceFileExists')->will($this->returnValue(true));

        $this->publisherFileMock->expects(
            $this->once()
        )->method(
            'isPublicationAllowed'
        )->will(
            $this->returnValue(true)
        );

        $this->publisherFileMock->expects(
            $this->once()
        )->method(
            'getSourcePath'
        )->will(
            $this->returnValue($sourcePath)
        );

        $this->publisherFileMock->expects(
            $this->once()
        )->method(
            'buildPublicViewFilename'
        )->will(
            $this->returnValue($result)
        );

        $uniquePath = 'unique\\' . $filePath;
        $this->publisherFileMock->expects(
            $this->once()
        )->method(
            'buildUniquePath'
        )->will(
            $this->returnValue($uniquePath)
        );

        $relativePath = 'relative\\' . $sourcePath;
        $this->rootDirectory->expects(
            $this->once()
        )->method(
            'getRelativePath'
        )->with(
            $this->equalTo($sourcePath)
        )->will(
            $this->returnValue($relativePath)
        );

        $this->rootDirectory->expects(
            $this->once()
        )->method(
            'stat'
        )->with(
            $this->equalTo($relativePath)
        )->will(
            $this->returnValue($timeSource)
        );

        $this->pubDirectory->expects(
            $this->once()
        )->method(
            'isExist'
        )->with(
            $this->equalTo($uniquePath)
        )->will(
            $this->returnValue($isExistsTarget)
        );

        if ($isExistsTarget) {
            $this->pubDirectory->expects(
                $this->once()
            )->method(
                'stat'
            )->with(
                $this->equalTo($uniquePath)
            )->will(
                $this->returnValue($timeTarget)
            );
        }

        if ($shouldBeUpdated) {
            if ($isFile) {
                $this->rootDirectory->expects(
                    $this->once()
                )->method(
                    'isFile'
                )->with(
                    $this->equalTo($relativePath)
                )->will(
                    $this->returnValue($isFile)
                );
                $this->rootDirectory->expects(
                    $this->once()
                )->method(
                    'copyFile'
                )->with(
                    $this->equalTo($relativePath),
                    $this->equalTo($uniquePath),
                    $this->equalTo($this->pubDirectory)
                )->will(
                    $this->returnSelf()
                );
                $this->pubDirectory->expects(
                    $this->once()
                )->method(
                    'touch'
                )->with(
                    $this->equalTo($uniquePath),
                    $this->equalTo($timeSource['mtime'])
                )->will(
                    $this->returnSelf()
                );
            } elseif (!$isDirectory) {
                $this->pubDirectory->expects(
                    $this->once()
                )->method(
                    'isDirectory'
                )->with(
                    $this->equalTo($uniquePath)
                )->will(
                    $this->returnValue(false)
                );
                $this->pubDirectory->expects(
                    $this->once()
                )->method(
                    'create'
                )->with(
                    $this->equalTo($uniquePath)
                )->will(
                    $this->returnSelf()
                );
            }
        }
        $this->viewFileSystem->expects(
            $this->once()
        )->method(
            'notifyViewFileLocationChanged'
        )->with(
            $this->equalTo($this->publisherFileMock)
        )->will(
            $this->returnSelf()
        );

        $this->assertSame($result, $this->publisher->getPublicFilePath($filePath, $params));
    }

    /**
     * @return array
     */
    public function getPublicFilePathDataProvider()
    {
        return array(
            'file that should be published mtime' => array(
                array(
                    'isExistsTarget' => true,
                    'timeSource' => array('mtime' => 121),
                    'timeTarget' => array('mtime' => 111),
                    'shouldBeUpdated' => true,
                    'isFile' => true,
                    'isDirectory' => false,
                    'result' => 'some/file/path.css'
                )
            ),
            'file that should be published not exist' => array(
                array(
                    'isExistsTarget' => false,
                    'timeSource' => array('mtime' => 111),
                    'timeTarget' => array('mtime' => 111),
                    'shouldBeUpdated' => false,
                    'isFile' => false,
                    'isDirectory' => false,
                    'result' => 'some/file/path.img'
                )
            ),
            'dir that should be published' => array(
                array(
                    'isExistsTarget' => true,
                    'timeSource' => array('mtime' => 121),
                    'timeTarget' => array('mtime' => 111),
                    'shouldBeUpdated' => true,
                    'isFile' => false,
                    'isDirectory' => true,
                    'result' => 'some/dir'
                )
            ),
            'not dir not a file' => array(
                array(
                    'isExistsTarget' => true,
                    'timeSource' => array('mtime' => 121),
                    'timeTarget' => array('mtime' => 111),
                    'shouldBeUpdated' => true,
                    'isFile' => false,
                    'isDirectory' => false,
                    'result' => 'some/interesting/path'
                )
            )
        );
    }

    /**
     * @param string $filePath
     * @param array $params
     */
    protected function prepareCommonMocks($filePath, $params)
    {
        $this->fileFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($filePath),
            $this->equalTo($params)
        )->will(
            $this->returnValue($this->publisherFileMock)
        );

        $this->preProcessorMock->expects(
            $this->once()
        )->method(
            'process'
        )->with(
            $this->equalTo($this->publisherFileMock),
            $this->equalTo($this->tmpDirectory)
        )->will(
            $this->returnValue($this->publisherFileMock)
        );
    }
}
