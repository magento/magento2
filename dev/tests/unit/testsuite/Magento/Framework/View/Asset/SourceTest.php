<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $rootDirRead;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $varDir;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $staticDirRead;

    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Cache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cache;

    /**
     * @var \Magento\Framework\View\Asset\PreProcessor\Pool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $preProcessorPool;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile|\PHPUnit_Framework_MockObject_MockObject
     */
    private $viewFileResolution;

    /**
     * @var \Magento\Framework\View\Design\ThemeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $theme;

    /**
     * @var Source
     */
    private $object;

    protected function setUp()
    {
        $this->cache = $this->getMock(
            'Magento\Framework\View\Asset\PreProcessor\Cache', [], [], '', false
        );
        $this->preProcessorPool = $this->getMock(
            'Magento\Framework\View\Asset\PreProcessor\Pool', [], [], '', false
        );
        $this->viewFileResolution = $this->getMock(
            'Magento\Framework\View\Design\FileResolution\Fallback\StaticFile', [], [], '', false
        );
        $this->theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');

        $themeList = $this->getMockForAbstractClass('Magento\Framework\View\Design\Theme\ListInterface');
        $themeList->expects($this->any())
            ->method('getThemeByFullPath')
            ->with('frontend/magento_theme')
            ->will($this->returnValue($this->theme));

        $this->initFilesystem();

        $this->object = new Source(
            $this->cache,
            $this->filesystem,
            $this->preProcessorPool,
            $this->viewFileResolution,
            $themeList
        );
    }

    public function testGetFileNoOriginalFile()
    {
        $this->viewFileResolution->expects($this->once())
            ->method('getFile')
            ->with('frontend', $this->theme, 'en_US', 'some/file.ext', 'Magento_Module')
            ->will($this->returnValue(false));
        $this->assertFalse($this->object->getFile($this->getAsset()));
    }

    public function testGetFileNoOriginalFileBasic()
    {
        $this->staticDirRead->expects($this->once())
            ->method('getAbsolutePath')
            ->with('some/file.ext')
            ->will($this->returnValue(false));
        $this->assertFalse($this->object->getFile($this->getAsset(false)));
    }

    public function testGetFileCached()
    {
        $root = '/root/some/file.ext';
        $expected = '/var/some/file.ext';
        $filePath = 'some/file.ext';
        $this->viewFileResolution->expects($this->once())
            ->method('getFile')
            ->with('frontend', $this->theme, 'en_US', $filePath, 'Magento_Module')
            ->will($this->returnValue($root));
        $this->rootDirRead->expects($this->once())
            ->method('getRelativePath')
            ->with($root)
            ->will($this->returnValue($filePath));
        $this->cache->expects($this->once())
            ->method('load')
            ->with("some/file.ext:{$filePath}")
            ->will($this->returnValue(serialize([DirectoryList::VAR_DIR, $filePath])));

        $this->varDir->expects($this->once())->method('getAbsolutePath')
            ->with($filePath)
            ->will($this->returnValue($expected));
        $this->assertSame($expected, $this->object->getFile($this->getAsset()));
    }

    /**
     * @param string $origFile
     * @param string $origPath
     * @param string $origContentType
     * @param string $origContent
     * @param string $isMaterialization
     * @dataProvider getFileDataProvider
     */
    public function testGetFile($origFile, $origPath, $origContentType, $origContent, $isMaterialization)
    {
        $filePath = 'some/file.ext';
        $cacheValue = "{$origPath}:{$filePath}";
        $this->viewFileResolution->expects($this->once())
            ->method('getFile')
            ->with('frontend', $this->theme, 'en_US', $filePath, 'Magento_Module')
            ->will($this->returnValue($origFile));
        $this->rootDirRead->expects($this->once())
            ->method('getRelativePath')
            ->with($origFile)
            ->will($this->returnValue($origPath));
        $this->cache->expects($this->once())
            ->method('load')
            ->will($this->returnValue(false));
        $this->rootDirRead->expects($this->once())
            ->method('readFile')
            ->with($origPath)
            ->will($this->returnValue($origContent));
        $processor = $this->getMockForAbstractClass('Magento\Framework\View\Asset\PreProcessorInterface');
        $this->preProcessorPool->expects($this->once())
            ->method('getPreProcessors')
            ->with($origContentType, 'ext')
            ->will($this->returnValue([$processor]));
        $processor->expects($this->once())
            ->method('process')
            ->will($this->returnCallback([$this, 'chainTestCallback']));
        if ($isMaterialization) {
            $this->varDir->expects($this->once())
                ->method('writeFile')
                ->with('view_preprocessed/source/some/file.ext', 'processed');
            $this->cache->expects($this->once())
                ->method('save')
                ->with(
                    serialize([DirectoryList::VAR_DIR, 'view_preprocessed/source/some/file.ext']),
                    $cacheValue
                );
            $this->varDir->expects($this->once())
                ->method('getAbsolutePath')
                ->with('view_preprocessed/source/some/file.ext')->will($this->returnValue('result'));
        } else {
            $this->varDir->expects($this->never())->method('writeFile');
            $this->cache->expects($this->once())
                ->method('save')
                ->with(serialize([DirectoryList::ROOT, 'source/some/file.ext']), $cacheValue);
            $this->rootDirRead->expects($this->once())
                ->method('getAbsolutePath')
                ->with('source/some/file.ext')
                ->will($this->returnValue('result'));
        }
        $this->assertSame('result', $this->object->getFile($this->getAsset()));
    }

    /**
     * @param string $path
     * @param string $expected
     * @dataProvider getContentTypeDataProvider
     */
    public function testGetContentType($path, $expected)
    {
        $this->assertEquals($expected, $this->object->getContentType($path));
    }

    /**
     * @return array
     */
    public function getContentTypeDataProvider()
    {
        return [
            ['', ''],
            ['path/file', ''],
            ['path/file.ext', 'ext'],
        ];
    }

    /**
     * A callback for affecting preprocessor chain in the test
     *
     * @param \Magento\Framework\View\Asset\PreProcessor\Chain $chain
     */
    public function chainTestCallback(\Magento\Framework\View\Asset\PreProcessor\Chain $chain)
    {
        $chain->setContentType('ext');
        $chain->setContent('processed');
    }

    /**
     * @return array
     */
    public function getFileDataProvider()
    {
        return [
            ['/root/some/file.ext', 'source/some/file.ext', 'ext', 'processed', false],
            ['/root/some/file.ext', 'source/some/file.ext', 'ext', 'not_processed', true],
            ['/root/some/file.ext2', 'source/some/file.ext2', 'ext2', 'processed', true],
            ['/root/some/file.ext2', 'source/some/file.ext2', 'ext2', 'not_processed', true],
        ];
    }

    protected function initFilesystem()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->rootDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->staticDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->varDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');

        $readDirMap = [
            [DirectoryList::ROOT, $this->rootDirRead],
            [DirectoryList::STATIC_VIEW, $this->staticDirRead],
            [DirectoryList::VAR_DIR, $this->varDir],
        ];

        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap($readDirMap));
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->will($this->returnValue($this->varDir));
    }

    /**
     * Create an asset mock
     *
     * @param bool $isFallback
     * @return \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAsset($isFallback = true)
    {
        if ($isFallback) {
            $context = new \Magento\Framework\View\Asset\File\FallbackContext(
                'http://example.com/static/',
                'frontend',
                'magento_theme',
                'en_US'
            );
        } else {
            $context = new \Magento\Framework\View\Asset\File\Context(
                'http://example.com/static/',
                DirectoryList::STATIC_VIEW,
                ''
            );
        }

        $asset = $this->getMock('Magento\Framework\View\Asset\File', [], [], '', false);
        $asset->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue($context));
        $asset->expects($this->any())
            ->method('getFilePath')
            ->will($this->returnValue('some/file.ext'));
        $asset->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('some/file.ext'));
        $asset->expects($this->any())
            ->method('getModule')
            ->will($this->returnValue('Magento_Module'));
        $asset->expects($this->any())
            ->method('getContentType')
            ->will($this->returnValue('ext'));

        return $asset;
    }
}
