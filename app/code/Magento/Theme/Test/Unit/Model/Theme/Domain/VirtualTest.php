<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme virtual model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Domain;

class VirtualTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test get existing staging theme
     *
     * @covers \Magento\Theme\Model\Theme\Domain\Virtual::__construct
     * @covers \Magento\Theme\Model\Theme\Domain\Virtual::getStagingTheme
     */
    public function testGetStagingThemeExisting()
    {
        $themeStaging = $this->createMock(\Magento\Theme\Model\Theme::class);

        $theme = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'getStagingVersion']);
        $theme->expects($this->once())->method('getStagingVersion')->willReturn($themeStaging);

        $themeFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $themeFactory->expects($this->never())->method('create');

        $themeCopyService = $this->createPartialMock(\Magento\Theme\Model\CopyService::class, ['copy']);
        $themeCopyService->expects($this->never())->method('copy');

        $customizationConfig = $this->createMock(\Magento\Theme\Model\Config\Customization::class);

        $object = new \Magento\Theme\Model\Theme\Domain\Virtual(
            $theme,
            $themeFactory,
            $themeCopyService,
            $customizationConfig
        );

        $this->assertSame($themeStaging, $object->getStagingTheme());
    }

    /**
     * Test creating staging theme
     *
     * @covers \Magento\Theme\Model\Theme\Domain\Virtual::_createStagingTheme
     * @covers \Magento\Theme\Model\Theme\Domain\Virtual::getStagingTheme
     */
    public function testGetStagingThemeNew()
    {
        $theme = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'getStagingVersion']);
        $theme->expects($this->once())->method('getStagingVersion')->willReturn(null);
        $appState = $this->createPartialMock(\Magento\Framework\App\State::class, ['getAreaCode']);
        $appState->expects($this->any())->method('getAreaCode')->willReturn('fixture_area');
        $appStateProperty = new \ReflectionProperty(\Magento\Theme\Model\Theme::class, '_appState');
        $appStateProperty->setAccessible(true);
        /** @var $theme \Magento\Framework\DataObject */
        $theme->setData(
            [
                'id' => 'fixture_theme_id',
                'theme_title' => 'fixture_theme_title',
                'preview_image' => 'fixture_preview_image',
                'is_featured' => 'fixture_is_featured',
                'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL,
            ]
        );
        $appStateProperty->setValue($theme, $appState);

        $themeStaging = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'setData', 'save']);
        $themeStaging->expects(
            $this->at(0)
        )->method(
            'setData'
        )->with(
            [
                'parent_id' => 'fixture_theme_id',
                'theme_path' => null,
                'theme_title' => 'fixture_theme_title - Staging',
                'preview_image' => 'fixture_preview_image',
                'is_featured' => 'fixture_is_featured',
                'type' => \Magento\Framework\View\Design\ThemeInterface::TYPE_STAGING,
            ]
        );
        $appStateProperty->setValue($themeStaging, $appState);
        $themeStaging->expects($this->at(1))->method('save');

        $themeFactory = $this->createPartialMock(\Magento\Theme\Model\ThemeFactory::class, ['create']);
        $themeFactory->expects($this->once())->method('create')->willReturn($themeStaging);

        $themeCopyService = $this->createPartialMock(\Magento\Theme\Model\CopyService::class, ['copy']);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $themeStaging);

        $customizationConfig = $this->createMock(\Magento\Theme\Model\Config\Customization::class);

        $object = new \Magento\Theme\Model\Theme\Domain\Virtual(
            $theme,
            $themeFactory,
            $themeCopyService,
            $customizationConfig
        );

        $this->assertSame($themeStaging, $object->getStagingTheme());
        $this->assertSame($themeStaging, $object->getStagingTheme());
    }

    /**
     * Test for is assigned method
     *
     * @covers \Magento\Theme\Model\Theme\Domain\Virtual::isAssigned
     */
    public function testIsAssigned()
    {
        $customizationConfig = $this->createPartialMock(
            \Magento\Theme\Model\Config\Customization::class,
            ['isThemeAssignedToStore']
        );
        $themeMock = $this->createPartialMock(
            \Magento\Theme\Model\Theme::class,
            ['__wakeup', 'getCollection', 'getId']
        );
        $customizationConfig->expects(
            $this->atLeastOnce()
        )->method(
            'isThemeAssignedToStore'
        )->with(
            $themeMock
        )->willReturn(
            true
        );
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            \Magento\Theme\Model\Theme\Domain\Virtual::class,
            ['theme' => $themeMock, 'customizationConfig' => $customizationConfig]
        );
        /** @var $model \Magento\Theme\Model\Theme\Domain\Virtual */
        $model = $objectManagerHelper->getObject(\Magento\Theme\Model\Theme\Domain\Virtual::class, $constructArguments);
        $this->assertTrue($model->isAssigned());
    }

    /**
     * @return array
     */
    public function physicalThemeDataProvider()
    {
        $physicalTheme = $this->getMockBuilder(\Magento\Framework\View\Design\ThemeInterface::class)
            ->setMethods(['isPhysical', 'getId'])
            ->getMockForAbstractClass();
        $physicalTheme->expects($this->once())
            ->method('isPhysical')
            ->willReturn(true);
        $physicalTheme->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        return [
            'empty' => [null],
            'theme' => [$physicalTheme],
        ];
    }

    /**
     * @test
     * @return void
     * @dataProvider physicalThemeDataProvider
     * @covers \Magento\Theme\Model\Theme\Domain\Virtual::getPhysicalTheme
     */
    public function testGetPhysicalTheme($data)
    {
        $themeMock = $this->createPartialMock(\Magento\Theme\Model\Theme::class, ['__wakeup', 'getParentTheme']);
        $parentThemeMock = $this->createPartialMock(
            \Magento\Theme\Model\Theme::class,
            ['__wakeup', 'isPhysical', 'getParentTheme']
        );

        $themeMock->expects($this->once())
            ->method('getParentTheme')
            ->willReturn($parentThemeMock);
        $parentThemeMock->expects($this->once())
            ->method('getParentTheme')
            ->willReturn($data);
        $parentThemeMock->expects($this->once())
            ->method('isPhysical')
            ->willReturn(false);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $object = $objectManagerHelper->getObject(
            \Magento\Theme\Model\Theme\Domain\Virtual::class,
            ['theme' => $themeMock]
        );
        /** @var $object \Magento\Theme\Model\Theme\Domain\Virtual */
        $this->assertEquals($data, $object->getPhysicalTheme());
    }
}
