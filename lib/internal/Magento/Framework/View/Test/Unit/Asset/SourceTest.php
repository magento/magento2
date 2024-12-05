<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\Context;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\PreProcessor\Chain;
use Magento\Framework\View\Asset\PreProcessor\ChainFactoryInterface;
use Magento\Framework\View\Asset\PreProcessor\Pool;
use Magento\Framework\View\Asset\Source;
use Magento\Framework\View\Design\FileResolution\Fallback\StaticFile;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SourceTest extends TestCase
{
    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var ReadInterface|MockObject
     */
    private $rootDirRead;

    /**
     * @var WriteInterface|MockObject
     */
    private $tmpDir;

    /**
     * @var WriteInterface|MockObject
     */
    private $staticDirRead;

    /**
     * @var Pool|MockObject
     */
    private $preProcessorPool;

    /**
     * @var StaticFile|MockObject
     */
    private $viewFileResolution;

    /**
     * @var ThemeInterface|MockObject
     */
    private $theme;

    /**
     * @var Source
     */
    private $object;

    /**
     * @var ChainFactoryInterface|MockObject
     */
    private $chainFactory;

    /**
     * @var Chain|MockObject
     */
    private $chain;

    /**
     * @var ReadFactory|MockObject
     */
    private $readFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->preProcessorPool = $this->createMock(Pool::class);
        $this->viewFileResolution = $this->createMock(
            StaticFile::class
        );
        $this->theme = $this->getMockForAbstractClass(ThemeInterface::class);
        /** @var ScopeConfigInterface $config */
        $this->chainFactory = $this->getMockBuilder(ChainFactoryInterface::class)
            ->getMock();
        $this->chain = $this->getMockBuilder(Chain::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isChanged', 'getContent', 'getTargetAssetPath'])
            ->getMock();
        $this->chainFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->chain);

        $themeProvider = $this->getMockForAbstractClass(ThemeProviderInterface::class);
        $themeProvider->expects($this->any())
            ->method('getThemeByFullPath')
            ->with('frontend/magento_theme')
            ->willReturn($this->theme);

        $this->readFactory = $this->createMock(ReadFactory::class);

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
     * @return void
     * @dataProvider getFileDataProvider
     */
    public function testGetFile($origFile, $origPath, $origContent, $isMaterialization, $isExist): void
    {
        $filePath = 'some/file.ext';
        $read = $this->createMock(Read::class);
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
            $this->tmpDir->expects($this->once())
                ->method('writeFile')
                ->with('some/file.ext', 'processed');
            $this->tmpDir->expects($this->once())
                ->method('getAbsolutePath')
                ->willReturn('view_preprocessed');
            $read->expects($this->once())
                ->method('getAbsolutePath')
                ->with('some/file.ext')
                ->willReturn('result');
        } else {
            $this->tmpDir->expects($this->never())->method('writeFile');
            $read
                ->method('readFile')
                ->with($origPath)
                ->willReturn($origContent);
            $read
                ->method('getAbsolutePath')
                ->with('file.ext')
                ->willReturn('result');
        }
        $this->assertSame('result', $this->object->getFile($this->getAsset()));
    }

    /**
     * @param string $path
     * @param string $expected
     *
     * @return void
     * @dataProvider getContentTypeDataProvider
     */
    public function testGetContentType($path, $expected): void
    {
        $this->assertEquals($expected, $this->object->getContentType($path));
    }

    /**
     * @return array
     */
    public static function getContentTypeDataProvider(): array
    {
        return [
            ['', ''],
            ['path/file', ''],
            ['path/file.ext', 'ext']
        ];
    }

    /**
     * A callback for affecting preprocessor chain in the test.
     *
     * @param Chain $chain
     *
     * @return void
     */
    public function chainTestCallback(Chain $chain): void
    {
        $chain->setContentType('ext');
        $chain->setContent('processed');
    }

    /**
     * @return array
     */
    public static function getFileDataProvider(): array
    {
        return [
            ['/root/some/file.ext', 'file.ext', 'processed', false, true],
            ['/root/some/file.ext', 'file.ext', 'not_processed', true, false],
            ['/root/some/file.ext2', 'file.ext2', 'processed', true, true],
            ['/root/some/file.ext2', 'file.ext2', 'not_processed', true, false]
        ];
    }

    /**
     * @return void
     */
    protected function initFilesystem(): void
    {
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->rootDirRead = $this->getMockForAbstractClass(
            ReadInterface::class
        );
        $this->staticDirRead = $this->getMockForAbstractClass(
            ReadInterface::class
        );
        $this->tmpDir = $this->getMockForAbstractClass(WriteInterface::class);

        $readDirMap = [
            [DirectoryList::ROOT, DriverPool::FILE, $this->rootDirRead],
            [DirectoryList::STATIC_VIEW, DriverPool::FILE, $this->staticDirRead],
            [DirectoryList::TMP_MATERIALIZATION_DIR, DriverPool::FILE, $this->tmpDir]
        ];

        $this->filesystem->expects($this->any())
            ->method('getDirectoryRead')
            ->willReturnMap($readDirMap);
        $this->filesystem->expects($this->any())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::TMP_MATERIALIZATION_DIR)
            ->willReturn($this->tmpDir);
    }

    /**
     * Create an asset mock.
     *
     * @param bool $isFallback
     *
     * @return File|MockObject
     */
    protected function getAsset($isFallback = true): File
    {
        if ($isFallback) {
            $context = new FallbackContext(
                'http://example.com/static/',
                'frontend',
                'magento_theme',
                'en_US'
            );
        } else {
            $context = new Context(
                'http://example.com/static/',
                DirectoryList::STATIC_VIEW,
                ''
            );
        }

        $asset = $this->createMock(File::class);
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
