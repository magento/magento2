<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\RequireJs\Test\Unit\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\RequireJs\Config;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\Repository;
use Magento\RequireJs\Model\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FileManagerTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var Filesystem|MockObject
     */
    private $fileSystem;

    /**
     * @var WriteInterface|MockObject
     */
    private $dir;

    /**
     * @var State|MockObject
     */
    private $appState;

    /**
     * @var File|MockObject
     */
    private $asset;

    /**
     * @var FileManager
     */
    private $object;

    /**
     * @var Repository|MockObject
     */
    private $assetRepoMock;

    protected function setUp(): void
    {
        $this->configMock = $this->createMock(Config::class);
        $this->fileSystem = $this->createMock(Filesystem::class);
        $this->appState = $this->createMock(State::class);
        $this->assetRepoMock = $this->createMock(Repository::class);
        $this->object = new FileManager($this->configMock, $this->fileSystem, $this->appState, $this->assetRepoMock);
        $this->dir = $this->getMockForAbstractClass(WriteInterface::class);
        $this->asset = $this->createMock(File::class);
    }

    /**
     * @param bool $exists
     * @dataProvider createRequireJsAssetDataProvider
     */
    public function testCreateRequireJsConfigAsset($exists)
    {
        $this->configMock->expects($this->once())
            ->method('getConfigFileRelativePath')
            ->willReturn('requirejs/file.js');
        $this->fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($this->dir);
        $this->assetRepoMock->expects($this->once())
            ->method('createArbitrary')
            ->with('requirejs/file.js', '')
            ->willReturn($this->asset);

        $this->appState->expects($this->once())->method('getMode')->willReturn('anything');
        $this->dir->expects($this->once())
            ->method('isExist')
            ->with('requirejs/file.js')
            ->willReturn($exists);
        if ($exists) {
            $this->configMock->expects($this->never())->method('getConfig');
            $this->dir->expects($this->never())->method('writeFile');
        } else {
            $data = 'requirejs config data';
            $this->configMock->expects($this->once())->method('getConfig')->willReturn($data);
            $this->dir->expects($this->once())->method('writeFile')->with('requirejs/file.js', $data);
        }
        $this->assertSame($this->asset, $this->object->createRequireJsConfigAsset());
    }

    /**
     * @return array
     */
    public function createRequireJsAssetDataProvider()
    {
        return [[true], [false]];
    }

    public function testCreateRequireJsAssetDevMode()
    {
        $this->configMock->expects($this->once())
            ->method('getConfigFileRelativePath')
            ->willReturn('requirejs/file.js');
        $this->fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($this->dir);
        $this->assetRepoMock->expects($this->once())
            ->method('createArbitrary')
            ->with('requirejs/file.js', '')
            ->willReturn($this->asset);

        $this->appState->expects($this->once())
            ->method('getMode')
            ->willReturn(State::MODE_DEVELOPER);
        $this->dir->expects($this->never())->method('isExist');
        $data = 'requirejs config data';
        $this->configMock->expects($this->once())->method('getConfig')->willReturn($data);
        $this->dir->expects($this->once())->method('writeFile')->with('requirejs/file.js', $data);
        $this->assertSame($this->asset, $this->object->createRequireJsConfigAsset());
    }

    public function testCreateBundleJsPool()
    {
        unset($this->configMock);
        $dirRead = $this->getMockBuilder(Read::class)
            ->setMockClassName('libDir')
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->createMock(FallbackContext::class);
        $assetRepo = $this->createMock(Repository::class);
        $config = $this->createMock(Config::class);

        $config
            ->expects($this->never())
            ->method('getConfigFileRelativePath')
            ->willReturn(null);

        $context
            ->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/bundle/dir');

        $dirRead
            ->expects($this->once())
            ->method('isExist')
            ->with('path/to/bundle/dir/js/bundle')
            ->willReturn(true);
        $dirRead
            ->expects($this->once())
            ->method('read')
            ->with('path/to/bundle/dir/js/bundle')
            ->willReturn(['bundle1.js', 'bundle2.js', 'some_file.not_js']);
        $dirRead
            ->expects($this->exactly(2))
            ->method('getRelativePath')
            ->willReturnMap([
                'path/to/bundle1.js',
                'path/to/bundle2.js'
            ]);
        $assetRepo
            ->expects($this->exactly(2))
            ->method('createArbitrary')
            ->willReturnMap([
                $this->asset,
                $this->asset
            ]);

        $assetRepo
            ->expects($this->once())
            ->method('getStaticViewFileContext')
            ->willReturn($context);

        $this->appState
            ->expects($this->once())
            ->method('getMode')
            ->willReturn('production');

        $this->fileSystem
            ->expects($this->once())
            ->method('getDirectoryRead')
            ->with('static')
            ->willReturn($dirRead);

        $object = new FileManager($config, $this->fileSystem, $this->appState, $assetRepo);

        $result = $object->createBundleJsPool();

        $this->assertArrayHasKey('0', $result);
        $this->assertArrayHasKey('1', $result);
    }

    public function testCreateMinResolverAsset()
    {
        $this->configMock
            ->expects($this->any())
            ->method('getMinResolverRelativePath')
            ->willReturn('relative path');
        $this->assetRepoMock
            ->expects($this->once())
            ->method('createArbitrary')
            ->with('relative path');
        $this->fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($this->dir);

        $this->object->createMinResolverAsset();
    }

    public function testCreateRequireJsMixinsAsset()
    {
        $path = 'relative path';
        $this->configMock
            ->expects($this->once())
            ->method('getMixinsFileRelativePath')
            ->willReturn($path);
        $this->assetRepoMock
            ->expects($this->once())
            ->method('createArbitrary')
            ->with($path, '')
            ->willReturn($this->asset);

        $this->assertSame($this->asset, $this->object->createRequireJsMixinsAsset());
    }

    public function testClearBundleJsPool()
    {
        $context = $this->getMockBuilder(FallbackContext::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->willReturn($this->dir);
        $this->assetRepoMock
            ->expects($this->once())
            ->method('getStaticViewFileContext')
            ->willReturn($context);
        $context->expects($this->once())
            ->method('getPath')
            ->willReturn('/path/to/directory');
        $this->dir->expects($this->once())
            ->method('delete')
            ->with('/path/to/directory/' . Config::BUNDLE_JS_DIR)
            ->willReturn(true);
        $this->assertTrue($this->object->clearBundleJsPool());
    }
}
