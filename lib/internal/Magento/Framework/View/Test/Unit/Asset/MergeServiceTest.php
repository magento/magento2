<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Asset;

use Magento\Framework\App\State;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write as DirectoryWrite;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\Merged;
use Magento\Framework\View\Asset\Merged as MergedViewAsset;
use Magento\Framework\View\Asset\MergeService;
use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Framework\View\Asset\MergeStrategy\Checksum;
use Magento\Framework\View\Asset\MergeStrategy\FileExists;
use Magento\Framework\View\Asset\Remote as RemoteViewAsset;
use Magento\Store\Model\Resolver\Store as StoreResolver;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Magento\Framework\App\ScopeInterface as AppScopeInterface;

/**
 * Class MergeServiceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MergeServiceTest extends TestCase
{
    /**
     * @var MergeService
     */
    protected $object;

    /**
     * @var ObjectManagerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var ConfigInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var Filesystem|PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var DirectoryWrite|PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryMock;

    /**
     * @var State|PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var StoreResolver|PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeResolverMock;

    /**
     * @var AppScopeInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $appScope;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(ConfigInterface::class)
            ->getMockForAbstractClass();
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeResolverMock = $this->getMockBuilder(StoreResolver::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryMock = $this->getMockBuilder(DirectoryWrite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->appScope = $this->getMockBuilder(AppScopeInterface::class)
            ->getMockForAbstractClass();

        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryMock);
        $this->storeResolverMock->expects($this->any())
            ->method('getScope')
            ->willReturn($this->appScope);

        $this->object = (new ObjectManager($this))->getObject(MergeService::class, [
            'objectManager' => $this->objectManagerMock,
            'config' => $this->configMock,
            'scopeResolver' => $this->storeResolverMock,
            'filesystem' => $this->filesystemMock,
            'state' => $this->stateMock,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Merge for content type 'unknown' is not supported.
     */
    public function testGetMergedAssetsWrongContentType()
    {
        $this->object->getMergedAssets([], 'unknown');
    }

    /**
     * @param array $assets
     * @param string $contentType
     * @param string $appMode
     * @param string $mergeStrategy
     *
     * @dataProvider getMergedAssetsDataProvider
     */
    public function testGetMergedAssets(array $assets, $contentType, $appMode, $mergeStrategy)
    {
        $mergedAsset = $this->createMock(AssetInterface::class);
        $mergeStrategyMock = $this->createMock($mergeStrategy);

        $this->configMock->expects($this->once())->method('isMergeCssFiles')->willReturn(true);
        $this->configMock->expects($this->once())->method('isMergeJsFiles')->willReturn(true);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Merged::class, ['assets' => $assets, 'mergeStrategy' => $mergeStrategyMock])
            ->willReturn($mergedAsset);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with($mergeStrategy)
            ->willReturn($mergeStrategyMock);
        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($appMode);

        $this->assertSame($mergedAsset, $this->object->getMergedAssets($assets, $contentType));
    }

    public static function getMergedAssetsDataProvider()
    {
        $jsAssets = [
            new RemoteViewAsset('http://127.0.0.1/magento/script_one.js'),
            new RemoteViewAsset('http://127.0.0.1/magento/script_two.js'),
        ];
        $cssAssets = [
            new RemoteViewAsset('http://127.0.0.1/magento/style_one.css'),
            new RemoteViewAsset('http://127.0.0.1/magento/style_two.css'),
        ];
        return [
            'js production mode' => [
                $jsAssets,
                'js',
                State::MODE_PRODUCTION,
                FileExists::class,
            ],
            'css production mode' => [
                $cssAssets,
                'css',
                State::MODE_PRODUCTION,
                FileExists::class,
            ],
            'js default mode' => [
                $jsAssets,
                'js',
                State::MODE_DEFAULT,
                FileExists::class,
            ],
            'css default mode' => [
                $cssAssets,
                'js',
                State::MODE_DEFAULT,
                FileExists::class,
            ],
            'js developer mode' => [
                $jsAssets,
                'js',
                State::MODE_DEVELOPER,
                Checksum::class,
            ],
            'css developer mode' => [
                $cssAssets,
                'css',
                AppState::MODE_DEVELOPER,
                Checksum::class,
            ]
        ];
    }

    public function testCleanMergedJsCss()
    {
        $mergedDir = MergedViewAsset::getRelativeDir();

        $this->directoryMock->expects($this->once())
            ->method('delete')
            ->with($mergedDir);

        $this->object->cleanMergedJsCss();
    }
}
