<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;

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

    /**
     * @var ChainFactoryInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $chainFactory;

    /**
     * @var Chain | \PHPUnit_Framework_MockObject_MockObject
     */
    private $chain;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readFactory;

    protected function setUp()
    {
        $this->preProcessorPool = $this->getMock(
            'Magento\Framework\View\Asset\PreProcessor\Pool', [], [], '', false
        );
        $this->viewFileResolution = $this->getMock(
            'Magento\Framework\View\Design\FileResolution\Fallback\StaticFile', [], [], '', false
        );
        $this->theme = $this->getMockForAbstractClass('Magento\Framework\View\Design\ThemeInterface');
        /** @var \Magento\Framework\App\Config\ScopeConfigInterface $config */

        $this->chainFactory = $this->getMockBuilder('Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface')
            ->getMock();
        $this->chain = $this->getMockBuilder('Magento\Framework\View\Asset\PreProcessor\Chain')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->chainFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->chain);

        $themeProvider = $this->getMock(ThemeProviderInterface::class);
        $themeProvider->expects($this->any())
            ->method('getThemeByFullPath')
            ->with('frontend/magento_theme')
            ->willReturn($this->theme);

        $this->readFactory = $this->getMock('Magento\Framework\Filesystem\Directory\ReadFactory', [], [], '', false);

        $this->initFilesystem();

        $this->object = (new ObjectManager($this))->getObject(Source::class, [
            'filesystem' => $this->filesystem,
            'readFactory' => $this->readFactory,
            'preProcessorPool' => $this->preProcessorPool,
            'fallback' => $this->viewFileResolution,
            'themeProvider' => $themeProvider,
            'chainFactory' => $this->chainFactory
        ]);
    }

    /**
     * @param string $origFile
     * @param string $origPath
     * @param string $origContent
     * @param bool $isMaterialization
     * @param bool $isExist
     *
     * @dataProvider getFileDataProvider
     */
    public function testGetFile($origFile, $origPath, $origContent, $isMaterialization, $isExist)
    {
        $filePath = 'some/file.ext';
        $read = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], '', false);
        $read->expects($this->at(0))->method('readFile')->with($origPath)->willReturn($origContent);
        $this->readFactory->expects($this->atLeastOnce())->method('create')->willReturn($read);
        $this->viewFileResolution->expects($this->once())
            ->method('getFile')
            ->with('frontend', $this->theme, 'en_US', $filePath, 'Magento_Module')
            ->willReturn($origFile);
        $this->preProcessorPool->expects($this->once())
            ->method('process')
            ->with($this->chain);
        $this->staticDirRead->expects($this->any())
            ->method('isExist')
            ->willReturn($isExist);
        if ($isMaterialization || !$isExist) {
            $this->chain
                ->expects($this->once())
                ->method('isChanged')
                ->willReturn(true);
            $this->chain
                ->expects($this->once())
                ->method('getContent')
                ->willReturn('processed');
            $this->chain
                ->expects($this->once())
                ->method('getTargetAssetPath')
                ->willReturn($filePath);
            $this->varDir->expects($this->once())
                ->method('writeFile')
                ->with('view_preprocessed/source/some/file.ext', 'processed');
            $this->varDir->expects($this->once())
                ->method('getAbsolutePath')
                ->willReturn('var');
            $read->expects($this->once())
                ->method('getAbsolutePath')
                ->with('view_preprocessed/source/some/file.ext')
                ->willReturn('result');
        } else {
            $this->varDir->expects($this->never())->method('writeFile');
            $read->expects($this->at(1))
                ->method('getAbsolutePath')
                ->with('file.ext')
                ->willReturn('result');
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
     * @param Chain $chain
     */
    public function chainTestCallback(Chain $chain)
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
            ['/root/some/file.ext', 'file.ext', 'processed', false, true],
            ['/root/some/file.ext', 'file.ext', 'not_processed', true, false],
            ['/root/some/file.ext2', 'file.ext2', 'processed', true, true],
            ['/root/some/file.ext2', 'file.ext2', 'not_processed', true, false],
        ];
    }

    protected function initFilesystem()
    {
        $this->filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $this->rootDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->staticDirRead = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\ReadInterface');
        $this->varDir = $this->getMockForAbstractClass('Magento\Framework\Filesystem\Directory\WriteInterface');

        $readDirMap = [
            [DirectoryList::ROOT, DriverPool::FILE, $this->rootDirRead],
            [DirectoryList::STATIC_VIEW, DriverPool::FILE, $this->staticDirRead],
            [DirectoryList::VAR_DIR, DriverPool::FILE, $this->varDir],
        ];

        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnMap($readDirMap);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::VAR_DIR)
            ->willReturn($this->varDir);
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
            ->willReturn($context);
        $asset->expects($this->any())
            ->method('getFilePath')
            ->willReturn('some/file.ext');
        $asset->expects($this->any())
            ->method('getPath')
            ->willReturn('some/file.ext');
        $asset->expects($this->any())
            ->method('getModule')
            ->willReturn('Magento_Module');
        $asset->expects($this->any())
            ->method('getContentType')
            ->willReturn('ext');

        return $asset;
    }
}
