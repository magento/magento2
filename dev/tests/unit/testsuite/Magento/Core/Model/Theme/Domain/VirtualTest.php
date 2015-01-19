<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme virtual model
 */
namespace Magento\Core\Model\Theme\Domain;

class VirtualTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test get existing staging theme
     *
     * @covers \Magento\Core\Model\Theme\Domain\Virtual::getStagingTheme
     */
    public function testGetStagingThemeExisting()
    {
        $themeStaging = $this->getMock('Magento\Core\Model\Theme', [], [], '', false, false);

        $theme = $this->getMock(
            'Magento\Core\Model\Theme',
            ['__wakeup', 'getStagingVersion'],
            [],
            '',
            false,
            false
        );
        $theme->expects($this->once())->method('getStagingVersion')->will($this->returnValue($themeStaging));

        $themeFactory = $this->getMock('Magento\Core\Model\ThemeFactory', ['create'], [], '', false);
        $themeFactory->expects($this->never())->method('create');

        $themeCopyService = $this->getMock('Magento\Theme\Model\CopyService', ['copy'], [], '', false);
        $themeCopyService->expects($this->never())->method('copy');

        $customizationConfig = $this->getMock('Magento\Theme\Model\Config\Customization', [], [], '', false);

        $object = new \Magento\Core\Model\Theme\Domain\Virtual(
            $theme,
            $themeFactory,
            $themeCopyService,
            $customizationConfig
        );

        $this->assertSame($themeStaging, $object->getStagingTheme());
        $this->assertSame($themeStaging, $object->getStagingTheme());
    }

    /**
     * Test creating staging theme
     *
     * @covers \Magento\Core\Model\Theme\Domain\Virtual::getStagingTheme
     */
    public function testGetStagingThemeNew()
    {
        $theme = $this->getMock(
            'Magento\Core\Model\Theme',
            ['__wakeup', 'getStagingVersion'],
            [],
            '',
            false,
            false
        );
        $theme->expects($this->once())->method('getStagingVersion')->will($this->returnValue(null));
        $appState = $this->getMock('Magento\Framework\App\State', ['getAreaCode'], [], '', false);
        $appState->expects($this->any())->method('getAreaCode')->will($this->returnValue('fixture_area'));
        $appStateProperty = new \ReflectionProperty('Magento\Core\Model\Theme', '_appState');
        $appStateProperty->setAccessible(true);
        /** @var $theme \Magento\Framework\Object */
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
            'Magento\Core\Model\Theme',
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

        $themeFactory = $this->getMock('Magento\Core\Model\ThemeFactory', ['create'], [], '', false);
        $themeFactory->expects($this->once())->method('create')->will($this->returnValue($themeStaging));

        $themeCopyService = $this->getMock('Magento\Theme\Model\CopyService', ['copy'], [], '', false);
        $themeCopyService->expects($this->once())->method('copy')->with($theme, $themeStaging);

        $customizationConfig = $this->getMock('Magento\Theme\Model\Config\Customization', [], [], '', false);

        $object = new \Magento\Core\Model\Theme\Domain\Virtual(
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
     * @covers \Magento\Core\Model\Theme\Domain\Virtual::isAssigned
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
            'Magento\Core\Model\Theme',
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
        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $constructArguments = $objectManagerHelper->getConstructArguments(
            'Magento\Core\Model\Theme\Domain\Virtual',
            ['theme' => $themeMock, 'customizationConfig' => $customizationConfig]
        );
        /** @var $model \Magento\Core\Model\Theme\Domain\Virtual */
        $model = $objectManagerHelper->getObject('Magento\Core\Model\Theme\Domain\Virtual', $constructArguments);
        $this->assertEquals(true, $model->isAssigned());
    }
}
