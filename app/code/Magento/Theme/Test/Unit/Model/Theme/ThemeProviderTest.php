<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManagerHelper($this);
    }

    public function testGetByFullPath()
    {
        $path = 'frontend/Magento/luma';
        $collectionFactory = $this->getMock(
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $collectionMock = $this->getMock(\Magento\Theme\Model\ResourceModel\Theme\Collection::class, [], [], '', false);
        $theme = $this->getMock(\Magento\Framework\View\Design\ThemeInterface::class, [], [], '', false);
        $collectionMock->expects(
            $this->once()
        )->method(
            'getThemeByFullPath'
        )->with(
            $path
        )->will(
            $this->returnValue($theme)
        );
        $collectionFactory->expects($this->once())->method('create')->will($this->returnValue($collectionMock));
        $themeFactory = $this->getMock(\Magento\Theme\Model\ThemeFactory::class, [], [], '', false);

        $themeProvider = $this->objectManager->getObject(
            \Magento\Theme\Model\Theme\ThemeProvider::class,
            [
                'collectionFactory' => $collectionFactory,
                'themeFactory' => $themeFactory
            ]
        );

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

        $this->assertSame($theme, $themeProvider->getThemeByFullPath($path));
    }

    public function testGetById()
    {
        $themeId = 755;
        $collectionFactory = $this->getMock(
            \Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class,
            [],
            [],
            '',
            false
        );
        $theme = $this->getMock(\Magento\Theme\Model\Theme::class, [], [], '', false);
        $theme->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $theme->expects($this->once())->method('load')->with($themeId)->will($this->returnSelf());
        $theme->expects($this->once())->method('getId')->will($this->returnValue(1));
        $theme->expects($this->once())->method('__sleep')->will($this->returnValue([]));

        $themeFactory = $this->getMock(\Magento\Theme\Model\ThemeFactory::class, ['create'], [], '', false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($theme));

        $cacheMock = $this->getMockBuilder(\Magento\Framework\App\CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cacheMock->expects($this->once())
            ->method('load')
            ->with('theme-by-id-' . $themeId)
            ->willReturn(false);

        $themeProvider = $this->objectManager->getObject(
            \Magento\Theme\Model\Theme\ThemeProvider::class,
            [
                'collectionFactory' => $collectionFactory,
                'themeFactory' => $themeFactory,
                'cache' => $cacheMock
            ]
        );

        $this->assertSame($theme, $themeProvider->getThemeById($themeId));
    }

    public function testGetThemeCustomizations()
    {
        $collectionFactory = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $themeFactory = $this->getMockBuilder(\Magento\Theme\Model\ThemeFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $collection = $this->getMockBuilder(\Magento\Theme\Model\ResourceModel\Theme\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $collectionFactory->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $collection->expects($this->once())
            ->method('addAreaFilter')
            ->with(Area::AREA_FRONTEND)
            ->willReturnSelf();
        $collection->expects($this->once())
            ->method('addTypeFilter')
            ->with(ThemeInterface::TYPE_VIRTUAL)
            ->willReturnSelf();

        $themeProvider = $this->objectManager->getObject(
            \Magento\Theme\Model\Theme\ThemeProvider::class,
            [
                'collectionFactory' => $collectionFactory,
                'themeFactory' => $themeFactory
            ]
        );

        $this->assertInstanceOf(get_class($collection), $themeProvider->getThemeCustomizations());
    }
}
