<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Test\Unit\Model;

use \Magento\RequireJs\Model\FileManager;
use Magento\Framework\App\Filesystem\DirectoryList;

class FileManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\RequireJs\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileSystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dir;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var \Magento\RequireJs\Model\FileManager
     */
    private $object;

    /**
     * @var \Magento\Framework\View\Asset\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $assetRepoMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(\Magento\Framework\RequireJs\Config::class);
        $this->fileSystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->appState = $this->createMock(\Magento\Framework\App\State::class);
        $this->assetRepoMock = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $this->object = new FileManager($this->configMock, $this->fileSystem, $this->appState, $this->assetRepoMock);
        $this->dir = $this->getMockForAbstractClass(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
        $this->asset = $this->createMock(\Magento\Framework\View\Asset\File::class);
    }

    /**
     * @param bool $exists
     * @dataProvider createRequireJsAssetDataProvider
     */
    public function testCreateRequireJsConfigAsset($exists)
    {
        $this->configMock->expects($this->once())
            ->method('getConfigFileRelativePath')
            ->will($this->returnValue('requirejs/file.js'));
        $this->fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->dir));
        $this->assetRepoMock->expects($this->once())
            ->method('createArbitrary')
            ->with('requirejs/file.js', '')
            ->will($this->returnValue($this->asset));

        $this->appState->expects($this->once())->method('getMode')->will($this->returnValue('anything'));
        $this->dir->expects($this->once())
            ->method('isExist')
            ->with('requirejs/file.js')
            ->will($this->returnValue($exists));
        if ($exists) {
            $this->configMock->expects($this->never())->method('getConfig');
            $this->dir->expects($this->never())->method('writeFile');
        } else {
            $data = 'requirejs config data';
            $this->configMock->expects($this->once())->method('getConfig')->will($this->returnValue($data));
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
            ->will($this->returnValue('requirejs/file.js'));
        $this->fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->dir));
        $this->assetRepoMock->expects($this->once())
            ->method('createArbitrary')
            ->with('requirejs/file.js', '')
            ->will($this->returnValue($this->asset));

        $this->appState->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));
        $this->dir->expects($this->never())->method('isExist');
        $data = 'requirejs config data';
        $this->configMock->expects($this->once())->method('getConfig')->will($this->returnValue($data));
        $this->dir->expects($this->once())->method('writeFile')->with('requirejs/file.js', $data);
        $this->assertSame($this->asset, $this->object->createRequireJsConfigAsset());
    }

    public function testCreateBundleJsPool()
    {
        unset($this->configMock);
        $dirRead = $this->getMockBuilder(\Magento\Framework\Filesystem\Directory\Read::class)
            ->setMockClassName('libDir')
            ->disableOriginalConstructor()
            ->getMock();
        $context = $this->createMock(\Magento\Framework\View\Asset\File\FallbackContext::class);
        $assetRepo = $this->createMock(\Magento\Framework\View\Asset\Repository::class);
        $config = $this->createMock(\Magento\Framework\RequireJs\Config::class);

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
            ->willReturn(['bundle1.js', 'bundle2.js']);
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
            ->will($this->returnValue($this->dir));

        $this->object->createMinResolverAsset();
    }

    public function testCreateRequireJsMixinsAsset()
    {
        $path = 'relative path';
        $this->configMock
            ->expects($this->once())
            ->method('getMixinsFileRelativePath')
            ->will($this->returnValue($path));
        $this->assetRepoMock
            ->expects($this->once())
            ->method('createArbitrary')
            ->with($path, '')
            ->willReturn($this->asset);

        $this->assertSame($this->asset, $this->object->createRequireJsMixinsAsset());
    }

    public function testClearBundleJsPool()
    {
        $context = $this->getMockBuilder(\Magento\Framework\View\Asset\File\FallbackContext::class)
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
            ->with('/path/to/directory/' . \Magento\Framework\RequireJs\Config::BUNDLE_JS_DIR)
            ->willReturn(true);
        $this->assertTrue($this->object->clearBundleJsPool());
    }
}
