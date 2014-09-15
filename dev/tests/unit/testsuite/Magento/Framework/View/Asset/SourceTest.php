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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Filesystem|\PHPUnit_Framework_MockObject_MockObject
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
            'Magento\Framework\View\Asset\PreProcessor\Cache', array(), array(), '', false
        );
        $this->preProcessorPool = $this->getMock(
            'Magento\Framework\View\Asset\PreProcessor\Pool', array(), array(), '', false
        );
        $this->viewFileResolution = $this->getMock(
            'Magento\Framework\View\Design\FileResolution\Fallback\StaticFile', array(), array(), '', false
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
            ->will($this->returnValue(serialize(array(\Magento\Framework\App\Filesystem::VAR_DIR, $filePath))));

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
            ->will($this->returnCallback(array($this, 'chainTestCallback')));
        if ($isMaterialization) {
            $this->varDir->expects($this->once())
                ->method('writeFile')
                ->with('view_preprocessed/source/some/file.ext', 'processed');
            $this->cache->expects($this->once())
                ->method('save')
                ->with(
                    serialize([\Magento\Framework\App\Filesystem::VAR_DIR, 'view_preprocessed/source/some/file.ext']),
                    $cacheValue
                );
            $this->varDir->expects($this->once())
                ->method('getAbsolutePath')
                ->with('view_preprocessed/source/some/file.ext')->will($this->returnValue('result'));
        } else {
            $this->varDir->expects($this->never())->method('writeFile');
            $this->cache->expects($this->once())
                ->method('save')
                ->with(serialize([\Magento\Framework\App\Filesystem::ROOT_DIR, 'source/some/file.ext']), $cacheValue);
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
        $this->filesystem = $this->getMock('Magento\Framework\App\Filesystem', array(), array(), '', false);
        $this->rootDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->staticDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->varDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');

        $readDirMap = [
            [\Magento\Framework\App\Filesystem::ROOT_DIR, $this->rootDirRead],
            [\Magento\Framework\App\Filesystem::STATIC_VIEW_DIR, $this->staticDirRead],
            [\Magento\Framework\App\Filesystem::VAR_DIR, $this->varDir],
        ];

        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->will($this->returnValueMap($readDirMap));
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(\Magento\Framework\App\Filesystem::VAR_DIR)
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
                \Magento\Framework\App\Filesystem::STATIC_VIEW_DIR,
                ''
            );
        }

        $asset = $this->getMock('Magento\Framework\View\Asset\File', array(), array(), '', false);
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
