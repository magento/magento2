<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\App\Area;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\Theme\ThemeProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class ThemeProviderTest
 * @covers \Magento\Theme\Model\Theme\ThemeProvider
 */
class ThemeProviderTest extends \PHPUnit_Framework_TestCase
{
    /** Theme path used by tests */
    const THEME_PATH = 'frontend/Magento/luma';

    /** Theme ID used by tests */
    const THEME_ID = 755;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManager;

    /** @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $collectionFactory;

    /** @var \Magento\Theme\Model\ThemeFactory|\PHPUnit_Framework_MockObject_MockObject  */
    private $themeFactory;

    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $cache;

    /** @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;

    /** @var \Magento\Theme\Model\Theme\ThemeProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $themeProvider;

    /** @var \Magento\Theme\Model\Theme|\PHPUnit_Framework_MockObject_MockObject */
    private $theme;

    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->collectionFactory = $this->getMock(
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->themeFactory = $this->getMock(
            \Magento\Theme\Model\ThemeFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->cache = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->serializer = $this->getMock(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->themeProvider = $this->objectManager->getObject(
            \Magento\Theme\Model\Theme\ThemeProvider::class,
            [
                'collectionFactory' => $this->collectionFactory,
                'themeFactory' => $this->themeFactory,
                'cache' => $this->cache,
                'serializer' => $this->serializer
            ]
        );
        $this->theme = $this->getMock(\Magento\Theme\Model\Theme::class, [], [], '', false);
    }

    public function testGetByFullPath()
    {
        $themeArray = ['theme_data' => 'theme_data'];
        $this->theme->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(self::THEME_ID);
        $this->theme->expects($this->exactly(2))
            ->method('toArray')
            ->willReturn($themeArray);

        $collectionMock = $this->getMock(\Magento\Theme\Model\ResourceModel\Theme\Collection::class, [], [], '', false);
        $collectionMock->expects($this->once())
            ->method('getThemeByFullPath')
            ->with(self::THEME_PATH)
            ->willReturn($this->theme);
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $this->serializer->expects($this->exactly(2))
            ->method('serialize')
            ->with($themeArray)
            ->willReturn('serialized theme');

        $deploymentConfig = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $deploymentConfig->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [\Magento\Framework\App\DeploymentConfig::class, $deploymentConfig],
            ]);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        // Assertion for first time load
        $this->assertSame($this->theme, $this->themeProvider->getThemeByFullPath(self::THEME_PATH));
        // Assertion for loading from local cache
        $this->assertSame($this->theme, $this->themeProvider->getThemeByFullPath(self::THEME_PATH));
    }

    public function testGetByFullPathWithCache()
    {
        $deploymentConfig = $this->getMockBuilder(\Magento\Framework\App\DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $deploymentConfig->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);

        $objectManagerMock = $this->getMock(\Magento\Framework\ObjectManagerInterface::class);
        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [\Magento\Framework\App\DeploymentConfig::class, $deploymentConfig],
            ]);
        \Magento\Framework\App\ObjectManager::setInstance($objectManagerMock);

        $serializedTheme = '{"theme_data":"theme_data"}';
        $themeArray = ['theme_data' => 'theme_data'];
        $this->theme->expects($this->once())
            ->method('populateFromArray')
            ->with($themeArray)
            ->willReturnSelf();
        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->theme);

        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedTheme)
            ->willReturn($themeArray);

        $this->cache->expects($this->once())
            ->method('load')
            ->with('theme' . self::THEME_PATH)
            ->willReturn($serializedTheme);

        // Assertion for load from cache
        $this->assertSame($this->theme, $this->themeProvider->getThemeByFullPath(self::THEME_PATH));
        // Assertion for load from object cache
        $this->assertSame($this->theme, $this->themeProvider->getThemeByFullPath(self::THEME_PATH));
    }

    public function testGetById()
    {
        $themeArray = ['theme_data' => 'theme_data'];
        $this->theme->expects($this->once())
            ->method('load')
            ->with(self::THEME_ID)
            ->willReturnSelf();
        $this->theme->expects($this->once())
            ->method('getId')
            ->willReturn(self::THEME_ID);
        $this->theme->expects($this->once())
            ->method('toArray')
            ->willReturn($themeArray);

        $this->themeFactory->expects($this->once())->method('create')->will($this->returnValue($this->theme));
        $this->cache->expects($this->once())
            ->method('load')
            ->with('theme-by-id-' . self::THEME_ID)
            ->willReturn(false);
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($themeArray)
            ->willReturn('{"theme_data":"theme_data"}');

        // Assertion for initial load
        $this->assertSame($this->theme, $this->themeProvider->getThemeById(self::THEME_ID));
        // Assertion for load from object cache
        $this->assertSame($this->theme, $this->themeProvider->getThemeById(self::THEME_ID));
    }

    public function testGetByIdWithCache()
    {
        $serializedTheme = '{"theme_data":"theme_data"}';
        $themeArray = ['theme_data' => 'theme_data'];
        $this->theme->expects($this->once())
            ->method('populateFromArray')
            ->with($themeArray)
            ->willReturnSelf();
        $this->cache->expects($this->once())
            ->method('load')
            ->with('theme-by-id-' . self::THEME_ID)
            ->willReturn($serializedTheme);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedTheme)
            ->willReturn($themeArray);
        $this->themeFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->theme);

        // Assertion for initial load from cache
        $this->assertSame($this->theme, $this->themeProvider->getThemeById(self::THEME_ID));
        // Assertion for load from object cache
        $this->assertSame($this->theme, $this->themeProvider->getThemeById(self::THEME_ID));
    }

    public function testGetThemeCustomizations()
    {
        $collection = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $collection->expects($this->once())
            ->method('addAreaFilter')
            ->with(Area::AREA_FRONTEND)
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('addTypeFilter')
            ->with(ThemeInterface::TYPE_VIRTUAL)
            ->willReturnSelf();
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);

        $this->assertInstanceOf(get_class($collection), $this->themeProvider->getThemeCustomizations());
    }
}
