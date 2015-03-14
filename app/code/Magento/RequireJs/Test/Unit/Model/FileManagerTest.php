<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\RequireJs\Test\Unit\Model;

use \Magento\RequireJs\Model\FileManager;
use Magento\Framework\App\Filesystem\DirectoryList;

class FileManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\RequireJs\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    private $config;

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
    private $assetRepo;

    protected function setUp()
    {
        $this->config = $this->getMock('\Magento\Framework\RequireJs\Config', [], [], '', false);
        $this->fileSystem = $this->getMock('\Magento\Framework\Filesystem', [], [], '', false);
        $this->appState = $this->getMock('\Magento\Framework\App\State', [], [], '', false);
        $this->assetRepo = $this->getMock('\Magento\Framework\View\Asset\Repository', [], [], '', false);
        $this->object = new FileManager($this->config, $this->fileSystem, $this->appState, $this->assetRepo);
        $this->dir = $this->getMockForAbstractClass('\Magento\Framework\Filesystem\Directory\WriteInterface');
        $this->asset = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
    }

    /**
     * @param bool $exists
     * @dataProvider createRequireJsAssetDataProvider
     */
    public function testCreateRequireJsConfigAsset($exists)
    {
        $this->config->expects($this->once())
            ->method('getConfigFileRelativePath')
            ->will($this->returnValue('requirejs/file.js'));
        $this->fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->dir));
        $this->assetRepo->expects($this->once())
            ->method('createArbitrary')
            ->with('requirejs/file.js', '')
            ->will($this->returnValue($this->asset));

        $this->appState->expects($this->once())->method('getMode')->will($this->returnValue('anything'));
        $this->dir->expects($this->once())
            ->method('isExist')
            ->with('requirejs/file.js')
            ->will($this->returnValue($exists));
        if ($exists) {
            $this->config->expects($this->never())->method('getConfig');
            $this->dir->expects($this->never())->method('writeFile');
        } else {
            $data = 'requirejs config data';
            $this->config->expects($this->once())->method('getConfig')->will($this->returnValue($data));
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
        $this->config->expects($this->once())
            ->method('getConfigFileRelativePath')
            ->will($this->returnValue('requirejs/file.js'));
        $this->fileSystem->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::STATIC_VIEW)
            ->will($this->returnValue($this->dir));
        $this->assetRepo->expects($this->once())
            ->method('createArbitrary')
            ->with('requirejs/file.js', '')
            ->will($this->returnValue($this->asset));

        $this->appState->expects($this->once())
            ->method('getMode')
            ->will($this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER));
        $this->dir->expects($this->never())->method('isExist');
        $data = 'requirejs config data';
        $this->config->expects($this->once())->method('getConfig')->will($this->returnValue($data));
        $this->dir->expects($this->once())->method('writeFile')->with('requirejs/file.js', $data);
        $this->assertSame($this->asset, $this->object->createRequireJsConfigAsset());
    }

    public function testCreateBundleJsPool()
    {
        unset($this->config);
        $dirRead = $this->getMock('Magento\Framework\Filesystem\Directory\Read', [], [], 'libDir', false);
        $context = $this->getMock('Magento\Framework\View\Asset\File\FallbackContext', [], [], '', false);
        $assetRepo = $this->getMock('Magento\Framework\View\Asset\Repository', [], [], '', false);
        $config = $this->getMock('\Magento\Framework\RequireJs\Config', [], [], '', false);

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
}
