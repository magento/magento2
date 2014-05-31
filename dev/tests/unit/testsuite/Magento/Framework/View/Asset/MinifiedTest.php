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
namespace Magento\Framework\View\Asset;

class MinifiedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\LocalInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_asset;

    /**
     * @var \Magento\Framework\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_staticViewDir;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_rootDir;

    /**
     * @var \Magento\Framework\Url|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_baseUrl;

    /**
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Code\Minifier\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_adapter;

    /**
     * @var \Magento\Framework\View\Asset\Minified
     */
    protected $_model;

    protected function setUp()
    {
        $this->_asset = $this->getMockForAbstractClass('\Magento\Framework\View\Asset\LocalInterface');
        $this->_logger = $this->getMock('\Magento\Framework\Logger', array(), array(), '', false);
        $this->_baseUrl = $this->getMock('\Magento\Framework\Url', array(), array(), '', false);
        $this->_staticViewDir = $this->getMockForAbstractClass(
            '\Magento\Framework\Filesystem\Directory\WriteInterface'
        );
        $this->_rootDir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->_filesystem = $this->getMock('\Magento\Framework\App\Filesystem', array(), array(), '', false);
        $this->_filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap([
                [\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR, $this->_staticViewDir],
                [\Magento\Framework\App\Filesystem::ROOT_DIR, $this->_rootDir],
            ]));
        $this->_filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR)
            ->will($this->returnValue($this->_staticViewDir));
        $this->_adapter = $this->getMockForAbstractClass('Magento\Framework\Code\Minifier\AdapterInterface');
        $this->_model = new Minified(
            $this->_asset,
            $this->_logger,
            $this->_filesystem,
            $this->_baseUrl,
            $this->_adapter
        );
    }

    /**
     * @param string $method
     * @param string $expected
     * @dataProvider inMemoryDecoratorDataProvider
     */
    public function testInMemoryDecorator($method, $expected)
    {
        $this->prepareRequestedAsMinifiedMock();
        $this->_adapter->expects($this->never())->method('minify');
        $this->assertSame($expected, $this->_model->$method());
        $this->assertSame($expected, $this->_model->$method()); // invoke second time to test in-memory caching
    }

    /**
     * Prepare case when an asset is requested explicitly with ".min" suffix
     *
     * In this case the minification is not supposed to occur
     */
    private function prepareRequestedAsMinifiedMock()
    {
        $this->_asset->expects($this->exactly(2))->method('getPath')->will($this->returnValue('test/library.min.js'));
        $this->_asset->expects($this->once())->method('getSourceFile')->will($this->returnValue('source_file'));
        $this->_asset->expects($this->once())->method('getFilePath')->will($this->returnValue('file_path'));
        $this->_asset->expects($this->once())->method('getContext')->will($this->returnValue('context'));
        $this->_asset->expects($this->once())->method('getUrl')->will($this->returnValue('url'));
    }

    /**
     * @return array
     */
    public function inMemoryDecoratorDataProvider()
    {
        return [
            ['getUrl', 'url'],
            ['getSourceFile', 'source_file'],
            ['getPath', 'test/library.min.js'],
            ['getFilePath', 'file_path'],
            ['getContext', 'context'],
        ];
    }

    /**
     * @param string $method
     * @param string $expected
     * @dataProvider assetDecoratorDataProvider
     */
    public function testAssetDecorator($method, $expected)
    {
        $this->_asset->expects($this->exactly(2))->method($method)->will($this->returnValue($expected));
        $this->assertSame($expected, $this->_model->$method());
        $this->assertSame($expected, $this->_model->$method()); // 2 times to ensure asset is invoked every time
    }

    /**
     * @return array
     */
    public function assetDecoratorDataProvider()
    {
        return [
            ['getContentType', 'content_type'],
            ['getModule', 'module'],
        ];
    }

    public function testGetContent()
    {
        $this->prepareRequestedAsMinifiedMock();
        $this->_adapter->expects($this->never())->method('minify');
        $this->_staticViewDir->expects($this->exactly(2))
            ->method('readFile')
            ->with('test/library.min.js')
            ->will($this->returnValue('content'));
        $this->assertEquals('content', $this->_model->getContent());
        $this->assertEquals('content', $this->_model->getContent());
    }

    public function testHasPreminifiedFile()
    {
        $this->_asset->expects($this->exactly(2))->method('getPath')->will($this->returnValue('test/library.js'));
        $this->_asset->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->will($this->returnValue('/foo/bar/test/library.js'));
        $this->_asset->expects($this->once())->method('getFilePath')->will($this->returnValue('file_path'));
        $this->_asset->expects($this->once())->method('getContext')->will($this->returnValue('context'));
        $this->_asset->expects($this->once())->method('getUrl')->will($this->returnValue('url'));
        $this->_rootDir->expects($this->once())
            ->method('getRelativePath')
            ->with('/foo/bar/test/library.min.js')
            ->will($this->returnValue('test/library.min.js'));
        $this->_rootDir->expects($this->once())
            ->method('isExist')
            ->with('test/library.min.js')
            ->will($this->returnValue(true));
        $this->_adapter->expects($this->never())->method('minify');
        $this->assertEquals('test/library.min.js', $this->_model->getPath());
    }

    public function testMinify()
    {
        $this->prepareAttemptToMinifyMock(false);
        $this->_asset->expects($this->once())->method('getContent')->will($this->returnValue('content'));
        $this->_adapter->expects($this->once())->method('minify')->with('content')->will($this->returnValue('mini'));
        $this->_staticViewDir->expects($this->once())->method('writeFile')->with($this->anything(), 'mini');
        $this->assertStringMatchesFormat('%s_library.min.js', $this->_model->getFilePath());
    }

    private function prepareAttemptToMinifyMock($fileExists, $rootDirExpectations = true)
    {
        $this->_asset->expects($this->atLeastOnce())->method('getPath')->will($this->returnValue('test/library.js'));
        $this->_asset->expects($this->atLeastOnce())
            ->method('getSourceFile')
            ->will($this->returnValue('/foo/bar/test/library.js'));
        if ($rootDirExpectations) {
            $this->_rootDir->expects($this->once())
                ->method('getRelativePath')
                ->with('/foo/bar/test/library.min.js')
                ->will($this->returnValue('test/library.min.js'));
            $this->_rootDir->expects($this->once())
                ->method('isExist')
                ->with('test/library.min.js')
                ->will($this->returnValue(false));
        }
        $this->_baseUrl->expects($this->once())->method('getBaseUrl')->will($this->returnValue('http://example.com/'));
        $this->_staticViewDir->expects($this->once())->method('isExist')->will($this->returnValue($fileExists));
    }

    public function testMinificationFailed()
    {
        $this->prepareAttemptToMinifyMock(false);
        $this->_asset->expects($this->once())->method('getContent')->will($this->returnValue('content'));
        $e = new \Exception('test');
        $this->_adapter->expects($this->once())->method('minify')->with('content')->will($this->throwException($e));
        $this->_logger->expects($this->once())->method('logException');
        $this->_staticViewDir->expects($this->never())->method('writeFile');
        $this->_asset->expects($this->once())->method('getFilePath')->will($this->returnValue('file_path'));
        $this->_asset->expects($this->once())->method('getContext')->will($this->returnValue('context'));
        $this->_asset->expects($this->once())->method('getUrl')->will($this->returnValue('url'));
        $this->assertEquals('test/library.js', $this->_model->getPath());
    }

    public function testShouldNotMinifyCozExists()
    {
        $this->prepareAttemptToMinifyMock(true);
        // IS_EXISTS is assumed by default, so nothing to mock here
        $this->_adapter->expects($this->never())->method('minify');
        $this->assertStringMatchesFormat('%s_library.min.js', $this->_model->getFilePath());
    }

    /**
     * @param int $mtimeOrig
     * @param int $mtimeMinified
     * @param bool $isMinifyExpected
     * @dataProvider minifyMtimeDataProvider
     */
    public function testMinifyMtime($mtimeOrig, $mtimeMinified, $isMinifyExpected)
    {
        $this->prepareAttemptToMinifyMock(true, false);
        $model = new Minified(
            $this->_asset,
            $this->_logger,
            $this->_filesystem,
            $this->_baseUrl,
            $this->_adapter,
            Minified::MTIME
        );
        $this->_rootDir->expects($this->any())
            ->method('getRelativePath')
            ->will($this->returnValueMap(array(
                array('/foo/bar/test/library.min.js', 'test/library.min.js'),
                array('/foo/bar/test/library.js', 'test/library.js'),
            )));
        $this->_rootDir->expects($this->once())
            ->method('isExist')
            ->with('test/library.min.js')
            ->will($this->returnValue(false));
        $this->_rootDir->expects($this->once())
            ->method('stat')
            ->with('test/library.js')
            ->will($this->returnValue(array('mtime' => $mtimeOrig)));
        $this->_staticViewDir->expects($this->once())
            ->method('stat')
            ->with($this->anything())
            ->will($this->returnValue(array('mtime' => $mtimeMinified)));
        if ($isMinifyExpected) {
            $this->_asset->expects($this->once())->method('getContent')->will($this->returnValue('content'));
            $this->_adapter->expects($this->once())
                ->method('minify')
                ->with('content')
                ->will($this->returnValue('mini'));
            $this->_staticViewDir->expects($this->once())->method('writeFile')->with($this->anything(), 'mini');
        } else {
            $this->_adapter->expects($this->never())->method('minify');
        }
        $this->assertStringMatchesFormat('%s_library.min.js', $model->getFilePath());
    }

    /**
     * @return array
     */
    public function minifyMtimeDataProvider()
    {
        return array(
            array(1, 2, true),
            array(3, 3, false),
        );
    }
}
