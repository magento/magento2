<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Theme;

use Magento\Framework\App\Area;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Theme\Model\ResourceModel\Theme\Collection;
use Magento\Theme\Model\Theme;
use Magento\Theme\Model\Theme\ThemeProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ThemeProviderTest extends TestCase
{
    /** Theme path used by tests */
    public const THEME_PATH = 'frontend/Magento/luma';

    /** Theme ID used by tests */
    public const THEME_ID = 755;

    /** @var ObjectManagerHelper */
    private $objectManager;

    /** @var \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory&MockObject */
    private $collectionFactory;

    /** @var \Magento\Theme\Model\ThemeFactory&MockObject  */
    private $themeFactory;

    /** @var CacheInterface&MockObject */
    private $cache;

    /** @var Json&MockObject */
    private $serializer;

    /** @var DeploymentConfig&MockObject */
    private DeploymentConfig $deploymentConfig;

    /** @var ThemeProvider|MockObject */
    private $themeProvider;

    /** @var \Magento\Theme\Model\Theme|MockObject */
    private $theme;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManagerHelper($this);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class,
            ['create']
        );
        $this->themeFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $this->cache = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->serializer = $this->createMock(Json::class);
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->themeProvider = $this->objectManager->getObject(
            ThemeProvider::class,
            [
                'collectionFactory' => $this->collectionFactory,
                'themeFactory' => $this->themeFactory,
                'cache' => $this->cache,
                'serializer' => $this->serializer,
                'deploymentConfig' => $this->deploymentConfig,
            ]
        );
        $this->theme = $this->createMock(Theme::class);
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
        $collectionMock = $this->createMock(Collection::class);
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
        $this->deploymentConfig->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
        $this->assertSame(
            $this->theme,
            $this->themeProvider->getThemeByFullPath(self::THEME_PATH),
            'Unable to load Theme'
        );
        $this->assertSame(
            $this->theme,
            $this->themeProvider->getThemeByFullPath(self::THEME_PATH),
            'Unable to load Theme from object cache'
        );
    }

    public function testGetByFullPathWithCache()
    {
        $this->deploymentConfig->expects($this->once())
            ->method('isDbAvailable')
            ->willReturn(true);
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
        $this->assertSame(
            $this->theme,
            $this->themeProvider->getThemeByFullPath(self::THEME_PATH),
            'Unable to load Theme from application cache'
        );
        $this->assertSame(
            $this->theme,
            $this->themeProvider->getThemeByFullPath(self::THEME_PATH),
            'Unable to load Theme from object cache'
        );
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

        $this->themeFactory->expects($this->once())->method('create')->willReturn($this->theme);
        $this->cache->expects($this->once())
            ->method('load')
            ->with('theme-by-id-' . self::THEME_ID)
            ->willReturn(false);
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($themeArray)
            ->willReturn('{"theme_data":"theme_data"}');

        $this->assertSame(
            $this->theme,
            $this->themeProvider->getThemeById(self::THEME_ID),
            'Unable to load Theme'
        );
        $this->assertSame(
            $this->theme,
            $this->themeProvider->getThemeById(self::THEME_ID),
            'Unable to load Theme from object cache'
        );
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

        $this->assertSame(
            $this->theme,
            $this->themeProvider->getThemeById(self::THEME_ID),
            'Unable to load Theme from application cache'
        );
        $this->assertSame(
            $this->theme,
            $this->themeProvider->getThemeById(self::THEME_ID),
            'Unable to load Theme from object cache'
        );
    }

    public function testGetThemeCustomizations()
    {
        $collection = $this->getMockBuilder(Collection::class)
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
