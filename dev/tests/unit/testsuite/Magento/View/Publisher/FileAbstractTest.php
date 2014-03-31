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
namespace Magento\View\Publisher;

class FileAbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\View\Publisher\FileAbstract|\PHPUnit_Framework_MockObject_MockObject */
    protected $fileAbstract;

    /** @var \Magento\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $filesystemMock;

    /** @var \Magento\View\Service|\PHPUnit_Framework_MockObject_MockObject */
    protected $serviceMock;

    /** @var \Magento\Module\Dir\Reader|\PHPUnit_Framework_MockObject_MockObject */
    protected $readerMock;

    /** @var \Magento\View\FileSystem|\PHPUnit_Framework_MockObject_MockObject */
    protected $viewFileSystem;

    /**
     * @var \Magento\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rootDirectory;

    /**
     * @param string $filePath
     * @param array $viewParams
     * @param null|string $sourcePath
     * @param null|string $fallback
     */
    protected function initModelMock($filePath, $viewParams, $sourcePath = null, $fallback = null)
    {
        $this->rootDirectory = $this->getMock('Magento\Filesystem\Directory\WriteInterface');

        $this->filesystemMock = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $this->filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryWrite'
        )->with(
            $this->equalTo(\Magento\App\Filesystem::ROOT_DIR)
        )->will(
            $this->returnValue($this->rootDirectory)
        );
        $this->serviceMock = $this->getMock('Magento\View\Service', array(), array(), '', false);
        $this->readerMock = $this->getMock('Magento\Module\Dir\Reader', array(), array(), '', false);
        $this->viewFileSystem = $this->getMock('Magento\View\FileSystem', array(), array(), '', false);
        if ($fallback) {
            $this->viewFileSystem->expects(
                $this->once()
            )->method(
                'getViewFile'
            )->with(
                $this->equalTo($filePath),
                $this->equalTo($viewParams)
            )->will(
                $this->returnValue('fallback\\' . $fallback)
            );

            $this->rootDirectory->expects(
                $this->once()
            )->method(
                'getRelativePath'
            )->with(
                'fallback\\' . $fallback
            )->will(
                $this->returnValue('related\\' . $fallback)
            );
        }

        $this->fileAbstract = $this->getMockForAbstractClass(
            'Magento\View\Publisher\FileAbstract',
            array(
                'filesystem' => $this->filesystemMock,
                'viewService' => $this->serviceMock,
                'modulesReader' => $this->readerMock,
                'viewFileSystem' => $this->viewFileSystem,
                'filePath' => $filePath,
                'allowDuplication' => true,
                'viewParams' => $viewParams,
                'sourcePath' => $sourcePath
            )
        );
    }

    /**
     * @param string $filePath
     * @param string $expected
     * @dataProvider getExtensionDataProvider
     */
    public function testGetExtension($filePath, $expected)
    {
        $this->initModelMock($filePath, array('some', 'array'));
        $this->assertSame($expected, $this->fileAbstract->getExtension());
    }

    /**
     * @return array
     */
    public function getExtensionDataProvider()
    {
        return array(array('some\path\file.css', 'css'), array('some\path\noextension', ''));
    }

    /**
     * @param string $filePath
     * @param bool $isExist
     * @param null|string $sourcePath
     * @param string|null $fallback
     * @param bool $expected
     * @internal param null|string $sourceFile
     * @dataProvider isSourceFileExistsDataProvider
     */
    public function testIsSourceFileExists($filePath, $isExist, $sourcePath, $fallback, $expected)
    {
        $this->initModelMock($filePath, array('some', 'array'), $sourcePath, $fallback);
        if ($fallback) {
            $this->rootDirectory->expects(
                $this->once()
            )->method(
                'isExist'
            )->with(
                'related\\' . $fallback
            )->will(
                $this->returnValue($isExist)
            );
        }

        $this->assertSame($expected, $this->fileAbstract->isSourceFileExists());
    }

    /**
     * @return array
     */
    public function isSourceFileExistsDataProvider()
    {
        return array(
            array(
                'filePath' => 'some\file',
                'isExist' => false,
                'sourcePath' => null,
                'fallback' => null,
                'expectedResult' => false
            ),
            array(
                'filePath' => 'some\file2',
                'isExist' => false,
                'sourcePath' => 'some\sourcePath',
                'fallback' => null,
                'expectedResult' => false
            ),
            array(
                'filePath' => 'some\file2',
                'isExist' => false,
                'sourcePath' => null,
                'fallback' => 'some\fallback\file',
                'expectedResult' => false
            ),
            array(
                'filePath' => 'some\file2',
                'isExist' => true,
                'sourcePath' => null,
                'fallback' => 'some\fallback\file',
                'expectedResult' => true
            )
        );
    }

    public function testGetFilePath()
    {
        $filePath = 'test\me';
        $this->initModelMock($filePath, array('some', 'array'));
        $this->assertSame($filePath, $this->fileAbstract->getFilePath());
    }

    public function testGetViewParams()
    {
        $viewParams = array('some', 'array');
        $this->initModelMock('some\file', $viewParams);
        $this->assertSame($viewParams, $this->fileAbstract->getViewParams());
    }

    public function testBuildPublicViewFilename()
    {
        $this->initModelMock('some\file', array());
        $this->serviceMock->expects($this->once())->method('getPublicDir')->will($this->returnValue('/some/pub/dir'));

        $this->fileAbstract->expects(
            $this->once()
        )->method(
            'buildUniquePath'
        )->will(
            $this->returnValue('some/path/to/file')
        );
        $this->assertSame('/some/pub/dir/some/path/to/file', $this->fileAbstract->buildPublicViewFilename());
    }

    /**
     * @param string $filePath
     * @param bool $isExist
     * @param null|string $sourcePath
     * @param string|null $fallback
     * @param bool $expected
     * @internal param null|string $sourceFile
     * @dataProvider getSourcePathDataProvider
     */
    public function testGetSourcePath($filePath, $isExist, $sourcePath, $fallback, $expected)
    {
        $this->initModelMock($filePath, array('some', 'array'), $sourcePath, $fallback);
        if ($fallback) {
            $this->rootDirectory->expects(
                $this->once()
            )->method(
                'isExist'
            )->with(
                'related\\' . $fallback
            )->will(
                $this->returnValue($isExist)
            );
        }

        $this->assertSame($expected, $this->fileAbstract->getSourcePath());
    }

    /**
     * @return array
     */
    public function getSourcePathDataProvider()
    {
        return array(
            array(
                'filePath' => 'some\file',
                'isExist' => false,
                'sourcePath' => null,
                'fallback' => null,
                'expectedResult' => null
            ),
            array(
                'filePath' => 'some\file2',
                'isExist' => false,
                'sourcePath' => 'some\sourcePath',
                'fallback' => null,
                'expectedResult' => null
            ),
            array(
                'filePath' => 'some\file2',
                'isExist' => false,
                'sourcePath' => null,
                'fallback' => 'some\fallback\file',
                'expectedResult' => null
            ),
            array(
                'filePath' => 'some\file2',
                'isExist' => true,
                'sourcePath' => null,
                'fallback' => 'some\fallback\file',
                'expectedResult' => 'fallback\some\fallback\file'
            )
        );
    }

    /**
     * @dataProvider sleepDataProvider
     */
    public function test__sleep($expected)
    {
        $this->initModelMock('some\file', array());
        $this->assertEquals($expected, $this->fileAbstract->__sleep());
    }

    /**
     * @return array
     */
    public function sleepDataProvider()
    {
        return array(
            array(
                array(
                    'filePath',
                    'extension',
                    'viewParams',
                    'sourcePath',
                    'allowDuplication',
                    'isPublicationAllowed',
                    'isFallbackUsed',
                    'isSourcePathProvided'
                )
            )
        );
    }
}
