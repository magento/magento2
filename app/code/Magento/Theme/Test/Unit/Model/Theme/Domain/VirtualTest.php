<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme virtual model
 */
namespace Magento\Theme\Test\Unit\Model\Theme\Domain;

class VirtualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get existing staging theme
     *
     * @covers \Magento\Theme\Model\Theme\Domain\Virtual::__construct
     * @covers \Magento\Theme\Model\Theme\Domain\Virtual::getStagingTheme
     */
    public function testGetStagingThemeExisting()
    {
        $themeStaging = $this->getMock('Magento\Theme\Model\Theme', [], [], '', false, false);

        $theme = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getStagingVersion'],
            [],
            '',
            false,
            false
        );
        $theme->expects($this->once())->method('getStagingVersion')->will($this->returnValue($themeStaging));

        $themeFactory = $this->getMock('Magento\Theme\Model\ThemeFactory', ['create'], [], '', false);
        $themeFactory->expects($this->never())->method('create');

        $themeCopyService = $this->getMock('Magento\Theme\Model\CopyService', ['copy'], [], '', false);
        $themeCopyService->expects($this->never())->method('copy');

        $customizationConfig = $this->getMock('Magento\Theme\Model\Config\Customization', [], [], '', false);

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
        $theme = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getStagingVersion'],
            [],
            '',
            false,
            false
        );
        $theme->expects($this->once())->method('getStagingVersion')->will($this->returnValue(null));
        $appState = $this->getMock('Magento\Framework\App\State', ['getAreaCode'], [], '', false);
        $appState->expects($this->any())->method('getAreaCode')->will($this->returnValue('fixture_area'));
        $appStateProperty = new \ReflectionProperty('Magento\Theme\Model\Theme', '_appState');
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

        $themeStaging = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'setData', 'save'],
            [],
            '',
            false,
            false
        );
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

        $themeFactory = $this->getMock('Magento\Theme\Model\ThemeFactory', ['create'], [], '', false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($themeStaging));

        $themeCopyService = $this->getMock('Magento\Theme\Model\CopyService', ['copy'], [], '', false);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $themeStaging);

        $customizationConfig = $this->getMock('Magento\Theme\Model\Config\Customization', [], [], '', false);

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
        $customizationConfig = $this->getMock(
            'Magento\Theme\Model\Config\Customization',
            ['isThemeAssignedToStore'],
            [],
            '',
            false
        );
        $themeMock = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getCollection', 'getId'],
            [],
            '',
            false,
            false
        );
        $customizationConfig->expects(
            $this->atLeastOnce()
        )->method(
            'isThemeAssignedToStore'
        )->with(
            $themeMock
        )->will(
            $this->returnValue(true)
        );
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Magento\Theme\Model\Theme\Domain\Virtual',
            ['theme' => $themeMock, 'customizationConfig' => $customizationConfig]
        );
        /** @var $model \Magento\Theme\Model\Theme\Domain\Virtual */
        $model = $objectManagerHelper->getObject('Magento\Theme\Model\Theme\Domain\Virtual', $constructArguments);
        $this->assertEquals(true, $model->isAssigned());
    }

    /**
     * @return array
     */
    public function physicalThemeDataProvider()
    {
        $physicalTheme = $this->getMockBuilder('Magento\Framework\View\Design\ThemeInterface')
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
        $themeMock = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'getParentTheme'],
            [],
            '',
            false,
            false
        );
        $parentThemeMock = $this->getMock(
            'Magento\Theme\Model\Theme',
            ['__wakeup', 'isPhysical', 'getParentTheme'],
            [],
            '',
            false,
            false
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
            'Magento\Theme\Model\Theme\Domain\Virtual',
            ['theme' => $themeMock]
        );
        /** @var $object \Magento\Theme\Model\Theme\Domain\Virtual */
        $this->assertEquals($data, $object->getPhysicalTheme());
    }
}
